<?php

namespace App\Console\Commands;

use App\Models\LoungeQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\Driver\Exception\ServerException;

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
            
            // Create MongoDB collection
            $this->info('Attempting to establish MongoDB connection...');
            $connection = DB::connection('mongodb');
            $this->info('MongoDB Connection established');
            
            $this->info('Attempting to select collection...');
            $model = new LoungeQueue();
            $collection = $connection->collection($model->getTable());
            $this->info('Collection selected: ' . $model->getTable());

            // Create indexes one by one with individual error handling
            $this->info('Creating indexes...');
            
            try {
                $this->info('Creating visitor_id index...');
                $collection->raw(function($collection) {
                    $collection->createIndex(
                        ['visitor_id' => 1],
                        [
                            'unique' => true,
                            'background' => true,
                            'name' => 'visitor_id_unique'
                        ]
                    );
                });
                $this->info('✓ Created visitor_id index');
            } catch (\Exception $e) {
                $this->warn('Failed to create visitor_id index: ' . $e->getMessage());
            }

            try {
                $this->info('Creating position index...');
                $collection->raw(function($collection) {
                    $collection->createIndex(
                        ['position' => 1],
                        [
                            'background' => true,
                            'name' => 'position_asc'
                        ]
                    );
                });
                $this->info('✓ Created position index');
            } catch (\Exception $e) {
                $this->warn('Failed to create position index: ' . $e->getMessage());
            }

            try {
                $this->info('Creating joined_at index...');
                $collection->raw(function($collection) {
                    $collection->createIndex(
                        ['joined_at' => 1],
                        [
                            'background' => true,
                            'name' => 'joined_at_asc'
                        ]
                    );
                });
                $this->info('✓ Created joined_at index');
            } catch (\Exception $e) {
                $this->warn('Failed to create joined_at index: ' . $e->getMessage());
            }

            try {
                $this->info('Creating user_id index...');
                $collection->raw(function($collection) {
                    $collection->createIndex(
                        ['user_id' => 1],
                        [
                            'background' => true,
                            'name' => 'user_id_asc'
                        ]
                    );
                });
                $this->info('✓ Created user_id index');
            } catch (\Exception $e) {
                $this->warn('Failed to create user_id index: ' . $e->getMessage());
            }

            $this->info('MongoDB setup completed.');
            
        } catch (\Exception $e) {
            Log::warning('MongoDB setup failed: ' . $e->getMessage());
            $this->error('MongoDB setup failed: ' . $e->getMessage());
            $this->error('Server error occurred. Please check your MongoDB URI and network connection.');
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
