<?php

namespace App\Events;

use App\DataTransfers\ProviderData;
use App\DataTransfers\VisitorData;
use App\Models\VisitorExamination;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VisitorExaminationCompletedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $examination;

    /**
     * Create a new event instance.
     */
    public function __construct(VisitorExamination $examination)
    {
        $this->examination = $examination;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $providerChannel = new Channel('provider.' . $this->examination->provider_id);
        $visitorChannel = new Channel('visitor.' . $this->examination->visitor_id);

        Log::info('VisitorExaminationCompletedEvent broadcasting on channels', [
            'provider_channel' => $providerChannel->name,
            'visitor_channel' => $visitorChannel->name,
            'examination_id' => $this->examination->id
        ]);
        
        return [$providerChannel, $visitorChannel];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $data = [
            'provider' => ProviderData::fromModel($this->examination->provider)->toArray(),
            'visitor' => VisitorData::fromModel($this->examination->visitor)->toArray(),
            'message' => 'Visitor examination completed',
            'started_at' => $this->examination->started_at->toISOString(),
            //'completed_at' => $this->examination->completed_at->toISOString(),
            'duration' => $this->examination->started_at->diffForHumans($this->examination->completed_at),
            'examination_id' => $this->examination->id
        ];

        Log::info('VisitorExaminationCompletedEvent broadcasting data', [
            'data' => $data
        ]);

        return $data;
    }

    /**
     * Get the name of the event to broadcast.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'visitor.examination.completed';
    }
} 