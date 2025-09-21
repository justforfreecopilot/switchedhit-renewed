<?php

// Require Composer autoloader
require 'vendor/autoload.php';

// Load environment variables from .env file
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos($line, '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

// Create F3 instance
$f3 = Base::instance();

// Set autoload path for controllers and models
$f3->set('AUTOLOAD', 'app/');

// Set UI path for templates
$f3->set('UI', 'ui/');

// Set JWT secret
$f3->set('JWT_SECRET', $env['JWT_SECRET']);

// Basic configuration
$f3->set('DEBUG', 3); // Set to 0 in production

// Error handling
$f3->set('ONERROR', function($f3) {
    echo 'An error occurred: ' . $f3->get('ERROR.text');
});

// Database connection
$db = new DB\SQL(
    'mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
    $env['DB_USER'],
    $env['DB_PASS']
);
$f3->set('DB', $db);

// Define routes
$f3->route('GET /', 'Home->index');
$f3->route('GET /login', 'Auth->login');
$f3->route('GET /register', 'Auth->register');

// Run the application
$f3->run();

?>