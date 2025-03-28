<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetupMongoDB extends Command
{
    protected $signature = 'mongodb:setup';
    protected $description = 'Setup MongoDB collections and indexes';

    public function handle()
    {
        try {
            $config = config('database.connections.mongodb');
            $this->info('MongoDB Connection Info:');
            $this->info('- Using DSN: ' . ($config['dsn'] ? 'Yes' : 'No'));
            if (!$config['dsn']) {
                $this->info('- Host: ' . $config['host']);
                $this->info('- Port: ' . $config['port']);
            }
            $this->info('- Database: ' . $config['database']);
            
            // Create MongoDB collection
            $connection = DB::connection('mongodb');
            $this->info('MongoDB Connection established');
            
            $collection = $connection->collection('waiting_room_queue');
            $this->info('Collection selected: waiting_room_queue');

            // Create indexes for better query performance
            $collection->raw(function($collection) {
                $collection->createIndex(['visitor_id' => 1], ['unique' => true]);
                $collection->createIndex(['position' => 1]);
                $collection->createIndex(['joined_at' => 1]);
            });

            $this->info('MongoDB collection and indexes created successfully.');
        } catch (\Exception $e) {
            Log::warning('MongoDB setup failed: ' . $e->getMessage());
            $this->error('MongoDB setup failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
} 