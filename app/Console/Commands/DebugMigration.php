<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Builder;

class DebugMigration extends Command
{
    protected $signature = 'migrate:debug';
    protected $description = 'Debug migration table prefix issue';

    public function handle()
    {
        $connection = DB::connection('mysql_primary');
        $prefix = $connection->getTablePrefix();
        
        $this->info('Table Prefix Type: ' . gettype($prefix));
        $this->info('Table Prefix Value: ' . print_r($prefix, true));
        
        // Check the connection's schema builder
        $schema = $connection->getSchemaBuilder();
        $this->info('Schema Builder Class: ' . get_class($schema));
        
        // Try to create migrations table
        try {
            $this->info('Attempting to create migrations table...');
            $schema->create('migrations', function ($table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
            $this->info('Successfully created migrations table');
        } catch (\Exception $e) {
            $this->error('Error creating migrations table: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
        
        // Check if migrations table exists
        try {
            $hasTable = $schema->hasTable('migrations');
            $this->info('Has migrations table: ' . ($hasTable ? 'yes' : 'no'));
        } catch (\Exception $e) {
            $this->error('Error checking migrations table: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
} 