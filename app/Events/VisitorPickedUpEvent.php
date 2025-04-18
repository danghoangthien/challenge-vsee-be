<?php

namespace App\Events;

use App\DataTransfers\ProviderData;
use App\DataTransfers\VisitorData;
use App\Models\VisitorExamination;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorPickedUpEvent implements ShouldBroadcastNow
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
        return [
            new Channel('visitor.' . $this->examination->visitor_id),
            new Channel('lounge.queue')
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'provider' => ProviderData::fromModel($this->examination->provider)->toArray(),
            'visitor' => VisitorData::fromModel($this->examination->visitor)->toArray(),
            'message' => 'A visitor has been picked up by a provider',
            'started_at' => $this->examination->started_at->toISOString(),
            'examination_id' => $this->examination->id,
            'reason' => $this->examination->reason
        ];
    }

    /**
     * Get the name of the event to broadcast.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'visitor.examination.pickedup';
    }
} 