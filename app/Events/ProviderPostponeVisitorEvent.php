<?php

namespace App\Events;

use App\Models\Visitor;
use App\Models\Provider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProviderPostponeVisitorEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Visitor $visitor,
        public readonly Provider $provider,
        public readonly string $message = 'The provider is currently busy and will attend to you soon'
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('visitor.' . $this->visitor->id),
        ];
    }
} 