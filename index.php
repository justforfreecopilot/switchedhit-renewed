<?php

// Check for static files before loading F3
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if (file_exists($file) && !is_dir($file)) {
    return false; // Let PHP serve the file
}

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

// Session management
// new Session();

// Error handling
$f3->set('ONERROR', function($f3) {
    $error = $f3->get('ERROR');
    echo 'Error: ' . $error['text'] . '<br>';
    echo 'Trace: <pre>' . $error['trace'] . '</pre>';
});

// Database connection
try {
    $db = new DB\SQL(
        'mysql:host=' . $env['DB_HOST'] . ';dbname=' . $env['DB_NAME'],
        $env['DB_USER'],
        $env['DB_PASS']
    );
    $f3->set('DB', $db);
} catch (Exception $e) {
    // Handle DB connection error
    $f3->set('DB', null);
}

// Define routes
$f3->route('GET /', 'Home->index');
$f3->route('GET /login', 'AuthController->login');
$f3->route('GET /register', 'AuthController->register');
$f3->route('GET /dashboard', 'Home->dashboard');

// API routes
$f3->route('POST /api/login', 'AuthController->apiLogin');
$f3->route('POST /api/register', 'AuthController->apiRegister');
$f3->route('GET /api/user/team', 'AuthController->apiUserTeam');
$f3->route('GET /api/user/me', 'AuthController->apiUserMe');

// Run the application
try {
    $f3->run();
} catch (Exception $e) {
    echo 'Exception: ' . $e->getMessage();
}

?>