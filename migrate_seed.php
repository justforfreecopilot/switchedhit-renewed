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

// Read and execute seed.sql
$sql = file_get_contents('db/seed.sql');
$db->exec($sql);

echo 'Seed executed successfully';

?>