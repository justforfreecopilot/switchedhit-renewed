<?php

require 'vendor/autoload.php';

// Load .env manually
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos($line, '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

try {
    $db = new DB\SQL(
        'mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
        $env['DB_USER'],
        $env['DB_PASS']
    );
    // Run a simple query
    $result = $db->exec('SELECT 1');
    echo 'Database connection successful' . "\n";

    // Check tables
    $tables = $db->exec('SHOW TABLES');
    echo 'Tables:' . "\n";
    foreach ($tables as $table) {
        echo $table['Tables_in_' . $env['DB_NAME']] . "\n";
    }
} catch (Exception $e) {
    echo 'Database connection failed: ' . $e->getMessage();
}

?>