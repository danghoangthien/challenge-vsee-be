<?php

namespace App\Listeners;

use App\Events\ClientEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleClientEvent implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ClientEvent $event): void
    {
        // Log the event data for debugging
        Log::info('Received client event', [
            'event' => $event->eventName,
            'data' => $event->data
        ]);

        // Handle different types of client events
        switch ($event->eventName) {
            case 'visitor-joined':
                $this->handleVisitorJoined($event->data);
                break;
            case 'visitor-left':
                $this->handleVisitorLeft($event->data);
                break;
            // Add more cases as needed
        }
    }

    /**
     * Handle visitor joined event
     */
    private function handleVisitorJoined(array $data): void
    {
        // Handle visitor joined logic
        Log::info('Visitor joined', $data);
    }

    /**
     * Handle visitor left event
     */
    private function handleVisitorLeft(array $data): void
    {
        // Handle visitor left logic
        Log::info('Visitor left', $data);
    }
}
