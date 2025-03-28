<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    // Create MongoDB collection
    $collection = DB::connection('mongodb')->collection('waiting_room_queue');

    // Create indexes for better query performance
    $collection->raw(function($collection) {
        $collection->createIndex(['visitor_id' => 1], ['unique' => true]);
        $collection->createIndex(['position' => 1]);
        $collection->createIndex(['joined_at' => 1]);
    });

    echo "MongoDB collection and indexes created successfully.\n";
} catch (\Exception $e) {
    Log::warning('MongoDB setup failed: ' . $e->getMessage());
    echo "MongoDB setup failed: " . $e->getMessage() . "\n";
} 