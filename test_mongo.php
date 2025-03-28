<?php
require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $connection = DB::connection('mongodb');
    echo "Connected successfully\n";
    
    $collection = $connection->getCollection('test');
    $collection->insertOne(['test' => 'connection']);
    echo "Database operation successful\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} 