<?php

require 'vendor/autoload.php';

// Load .env
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos($line, '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

$db = new DB\SQL(
    'mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
    $env['DB_USER'],
    $env['DB_PASS']
);

// Drop existing tables if they exist
$db->exec('DROP TABLE IF EXISTS teams');
$db->exec('DROP TABLE IF EXISTS users');

// Read and execute schema.sql
$sql = file_get_contents('db/schema.sql');
$db->exec($sql);

echo 'Schema executed successfully';

?>