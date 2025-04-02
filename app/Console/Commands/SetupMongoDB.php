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
            $mongoUri = env('MONGODB_URI');
            $this->info('MongoDB Connection Info:');
            $this->info('- Using URI: ' . ($mongoUri ? 'Yes' : 'No'));
            if ($mongoUri) {
                $this->info('- Database: ' . parse_url($mongoUri, PHP_URL_PATH));
            }
            
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