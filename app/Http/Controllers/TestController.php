<?php

namespace App\Http\Controllers;

use App\Events\TestEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class TestController extends Controller
{
    public function sendTestEvent()
    {
        try {
            $message = "Hello from Laravel! Sent at " . Carbon::now()->format('Y-m-d H:i:s');
            Log::info('Broadcasting test event', [
                'message' => $message,
                'channel' => 'test-channel',
                'event' => 'test-event'
            ]);

            event(new TestEvent($message));
            
            Log::info('Event broadcasted successfully');

            return response()->json([
                'message' => 'Event sent successfully',
                'event_data' => [
                    'message' => $message,
                    'timestamp' => Carbon::now()->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error broadcasting event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error sending event',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 