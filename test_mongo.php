<?php

$uri = "mongodb+srv://danghoangthien:Q6b4Mo8cddDbQViz@challege-vsee-cluster.qun9h9b.mongodb.net/vsee";
$options = [
    'authSource' => 'admin',
    'retryWrites' => true,
    'w' => 'majority',
    'tls' => true,
    'tlsAllowInvalidCertificates' => true,
    'tlsAllowInvalidHostnames' => true
];

try {
    $manager = new MongoDB\Driver\Manager($uri, $options);
    echo "Connected successfully\n";
    
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $manager->executeCommand('vsee', $command);
    echo "Database ping successful\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} 