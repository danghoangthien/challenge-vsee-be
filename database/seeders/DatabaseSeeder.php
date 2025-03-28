<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            UserSeeder::class,
            ProviderSeeder::class,
            VisitorSeeder::class
        ]);
    }

    /**
     * Get the database connection that should be used by the seeder.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        return \Illuminate\Support\Facades\DB::connection('mysql_primary');
    }
}
