<?php

namespace App\Services;

use App\Models\LoungeQueue;
use App\Models\Visitor;
use App\Models\VisitorExamination;
use Illuminate\Support\Facades\DB;
use App\Events\VisitorPickedUpEvent;
use App\Events\ProviderPickedUpVisitorEvent;
use App\Events\ProviderPostponeVisitorEvent;
use App\Services\RedisLock;
use App\Models\Provider;
use App\Events\VisitorExitedEvent;
use App\Exceptions\ProviderBusyException;
use App\Exceptions\NotFoundException;
use App\Exceptions\VisitorAlreadyInQueueException;

class LoungeQueueService
{
    private const LOCK_PREFIX = 'lounge_queue';
    private const LOCK_POSITION = self::LOCK_PREFIX . ':position:global';

    protected $redisLock;

    public function __construct(RedisLock $redisLock)
    {
        $this->redisLock = $redisLock;
    }

    /**
     * Enqueue a visitor to the lounge queue.
     */
    public function enqueueVisitor(Visitor $visitor): int
    {
        // Check if visitor is already in queue
        $existingQueue = LoungeQueue::where('visitor_id', $visitor->id)->first();

        if ($existingQueue) {
            throw new VisitorAlreadyInQueueException();
        }

        // Get the last position in queue
        $lastPosition = LoungeQueue::orderBy('position', 'desc')
            ->value('position') ?? 0;

        // Create new queue entry
        LoungeQueue::create([
            'visitor_id' => $visitor->id,
            'user_id' => $visitor->user_id,
            'position' => $lastPosition + 1,
            'joined_at' => now(),
        ]);

        return $lastPosition + 1;
    }

    /**
     * Get the list of visitors in the queue.
     */
    public function getWaitingList(): array
    {
        $waitingList = LoungeQueue::orderBy('position', 'asc')
            ->with(['visitor', 'visitor.user'])
            ->get()
            ->map(function ($queue) {
                return [
                    'position' => $queue->position,
                    'visitor_id' => $queue->visitor_id,
                    'visitor_name' => $queue->visitor->user->name ?? 'Unknown',
                    'joined_at' => $queue->joined_at,
                    'reason' => $queue->reason,
                    'waiting_time' => $queue->joined_at->diffForHumans()
                ];
            });

        return [
            'success' => true,
            'data' => [
                'total' => $waitingList->count(),
                'visitors' => $waitingList
            ]
        ];
    }

    /**
     * Pick up a visitor from the queue.
     */
    public function pickupVisitor(Provider $provider, ?string $visitorId = null): array
    {
        // Check if provider is already examining another visitor
        $inProgressExamination = VisitorExamination::where('provider_id', $provider->id)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgressExamination) {
            // If visitor ID is provided, notify that specific visitor
            if ($visitorId) {
                $visitor = Visitor::find($visitorId);
                if ($visitor) {
                    event(new ProviderPostponeVisitorEvent($visitor, $provider));
                }
            }
            throw new ProviderBusyException();
        }

        // Get queue entry based on visitor_id if provided, otherwise get first in queue
        $queueEntry = null;
        if ($visitorId) {
            $queueEntry = LoungeQueue::where('visitor_id', $visitorId)->first();
            
            if (!$queueEntry) {
                throw new NotFoundException('Visitor not found in waiting queue');
            }
        } else {
            $queueEntry = LoungeQueue::orderBy('position', 'asc')->first();

            if (!$queueEntry) {
                throw new NotFoundException('No visitors in waiting queue');
            }
        }

        // Use Redis lock to prevent race conditions
        return $this->redisLock->executeWithLock(self::LOCK_POSITION, function () use ($queueEntry, $provider) {
            DB::beginTransaction();
            try {
                // Double check provider is not examining another visitor
                $inProgressExamination = VisitorExamination::where('provider_id', $provider->id)
                    ->where('status', 'in_progress')
                    ->first();

                if ($inProgressExamination) {
                    throw new ProviderBusyException();
                }

                // Create visitor examination record
                $examination = VisitorExamination::create([
                    'visitor_id' => $queueEntry->visitor_id,
                    'provider_id' => $provider->id,
                    'queue_entry_id' => $queueEntry->id,
                    'started_at' => now(),
                    'status' => 'in_progress'
                ]);

                // Remove visitor from queue and update positions
                $queueEntry->delete();
                LoungeQueue::where('position', '>', $queueEntry->position)
                    ->update(['position' => DB::raw('position - 1')]);

                // Dispatch events to notify both visitor and provider
                event(new VisitorPickedUpEvent($examination));
                event(new ProviderPickedUpVisitorEvent($examination));

                DB::commit();

                return [
                    'success' => true,
                    'data' => [
                        'visitor_id' => $queueEntry->visitor_id,
                        'visitor_name' => $queueEntry->visitor->user->name ?? 'Unknown',
                        'waited_time' => $queueEntry->joined_at->diffForHumans(),
                        'examination_id' => $examination->id,
                        'message' => 'Successfully picked up visitor from queue'
                    ]
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    /**
     * Complete a visitor examination.
     */
    public function dropoffVisitor(Provider $provider, string $visitorId): array
    {
        // Get examination record
        $examination = VisitorExamination::where('visitor_id', $visitorId)
            ->where('provider_id', $provider->id)
            ->where('status', 'in_progress')
            ->first();
        
        if (!$examination) {
            throw new NotFoundException('Visitor examination not found or not in progress');
        }

        // Update examination record
        $examination->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return [
            'success' => true,
            'data' => [
                'visitor_id' => $examination->visitor_id,
                'visitor_name' => $examination->visitor->user->name ?? 'Unknown',
                'examination_duration' => $examination->started_at->diffForHumans($examination->completed_at),
                'message' => 'Successfully completed visitor examination'
            ]
        ];
    }

    /**
     * Remove a visitor from the queue.
     */
    public function exitQueue(Visitor $visitor): array
    {
        // Find the visitor's queue entry
        $queueEntry = LoungeQueue::where('visitor_id', $visitor->id)->first();
        
        if (!$queueEntry) {
            throw new NotFoundException('Visitor not found in waiting queue');
        }

        $position = $queueEntry->position;
        $waitedTime = $queueEntry->joined_at->diffForHumans();

        // Use Redis lock to prevent race conditions
        return $this->redisLock->executeWithLock(self::LOCK_POSITION, function () use ($queueEntry, $position, $visitor, $waitedTime) {
            DB::beginTransaction();
            try {
                // Remove from queue and update positions
                $queueEntry->delete();
                LoungeQueue::where('position', '>', $position)
                    ->update(['position' => DB::raw('position - 1')]);

                // Dispatch event to notify about visitor exit
                event(new VisitorExitedEvent($visitor, $position, $waitedTime));

                DB::commit();

                return [
                    'success' => true,
                    'data' => [
                        'visitor_id' => $visitor->id,
                        'visitor_name' => $visitor->user->name ?? 'Unknown',
                        'waited_time' => $waitedTime,
                        'message' => 'Successfully removed from queue'
                    ]
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }
} 