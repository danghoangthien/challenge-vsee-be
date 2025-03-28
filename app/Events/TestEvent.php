<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TestEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message = 'Hello from Laravel!')
    {
        $this->message = $message;
        Log::info('TestEvent constructed', ['message' => $message]);
    }

    public function broadcastOn(): array
    {
        Log::info('TestEvent broadcastOn called', ['channel' => 'test-channel']);
        return [new Channel('test-channel')];
    }

    public function broadcastAs(): string
    {
        Log::info('TestEvent broadcastAs called', ['event_name' => 'test-event']);
        return 'test-event';
    }

    public function broadcastWith(): array
    {
        $data = [
            'messages' => $this->message,
            'timestamp' => Carbon::now()->toIso8601String()
        ];
        Log::info('TestEvent broadcastWith called', ['data' => $data]);
        return $data;
    }
} 