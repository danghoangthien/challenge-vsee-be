<?php

namespace App\Events;

use App\Models\Visitor;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorExitedQueue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Visitor $visitor
    ) {}

    public function broadcastOn()
    {
        return [
            new Channel('visitor.' . $this->visitor->id),
            new Channel('lounge.queue')
        ];
    }

    /**
     * Get the name of the event to broadcast.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'visitor.exited.queue';
    }
} 