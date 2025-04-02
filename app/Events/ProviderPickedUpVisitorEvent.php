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
use Illuminate\Support\Facades\Log;

class ProviderPickedUpVisitorEvent implements ShouldBroadcastNow
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
        $channel = new Channel('provider.' . $this->examination->provider_id);
        Log::info('ProviderPickedUpVisitorEvent broadcasting on channel', [
            'channel' => $channel->name,
            'provider_id' => $this->examination->provider_id,
            'examination_id' => $this->examination->id
        ]);
        
        return [
            $channel,
            new Channel('providers')
        ];
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
            'message' => 'You have picked up a visitor',
            'started_at' => $this->examination->started_at->toISOString(),
            'examination_id' => $this->examination->id
        ];

        Log::info('ProviderPickedUpVisitorEvent broadcasting data', [
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
        return 'provider.pickedup.visitor';
    }
}
