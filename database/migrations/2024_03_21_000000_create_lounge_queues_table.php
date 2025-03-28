<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Client;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $client = new Client(config('database.connections.mongodb.dsn'));
        $database = config('database.connections.mongodb.database');
        
        $collection = $client->$database->waiting_room_queue;
        
        // Create indexes for better query performance
        $collection->createIndex(['visitor_id' => 1], ['unique' => true]);
        $collection->createIndex(['position' => 1]);
        $collection->createIndex(['joined_at' => 1]);

        // Add reason field to documents that don't have it
        $collection->updateMany(
            ['reason' => ['$exists' => false]],
            ['$set' => ['reason' => null]]
        );

        // Remove status-related fields
        $collection->updateMany(
            [],
            [
                '$unset' => [
                    'status' => '',
                    'picked_up_at' => '',
                    'picked_up_by' => '',
                    'completed_at' => ''
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $client = new Client(config('database.connections.mongodb.dsn'));
        $database = config('database.connections.mongodb.database');
        
        $collection = $client->$database->waiting_room_queue;
        
        // Drop indexes
        $collection->dropIndex(['visitor_id' => 1]);
        $collection->dropIndex(['position' => 1]);
        $collection->dropIndex(['joined_at' => 1]);

        // Remove reason field
        $collection->updateMany(
            [],
            ['$unset' => ['reason' => '']]
        );

        // Restore status fields with default values
        $collection->updateMany(
            [],
            [
                '$set' => [
                    'status' => 'waiting',
                    'picked_up_at' => null,
                    'picked_up_by' => null,
                    'completed_at' => null
                ]
            ]
        );
    }
}; 