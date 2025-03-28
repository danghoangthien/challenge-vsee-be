<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomMigrate extends Command
{
    protected $signature = 'migrate:custom {--seed} {--fresh}';
    protected $description = 'Run migrations with custom handling for MongoDB';

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Dropping all tables...');
            Schema::connection('mysql_primary')->dropAllTables();
        }

        // First, ensure migrations table exists
        $this->info('Creating migrations table if it does not exist...');
        if (!Schema::connection('mysql_primary')->hasTable('migrations')) {
            Schema::connection('mysql_primary')->create('migrations', function ($table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
        }

        // Get all migration files
        $migrations = glob(database_path('migrations/*.php'));
        $batch = 1;

        foreach ($migrations as $migration) {
            $migrationName = basename($migration, '.php');
            
            // Skip if migration already exists and not doing fresh
            if (!$this->option('fresh')) {
                $exists = DB::connection('mysql_primary')
                    ->table('migrations')
                    ->where('migration', $migrationName)
                    ->exists();

                if ($exists) {
                    $this->info("Migration {$migrationName} already exists, skipping...");
                    continue;
                }
            }

            $this->info("Running migration: {$migrationName}");
            
            // Include and run the migration
            $migrationClass = require $migration;
            $migrationClass->up();

            // Record the migration
            DB::connection('mysql_primary')->table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch,
            ]);
        }

        // Run seeders if requested
        if ($this->option('seed')) {
            $this->info('Running seeders...');
            $this->call('db:seed', [
                '--database' => 'mysql_primary',
                '--force' => true,
            ]);
        }
    }
} 