<?php
// Load Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Define global constants for environment and app settings
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_NAME', $_ENV['APP_NAME'] ?? 'BurolES');
define('DEBUG_MODE', APP_ENV === 'local');

// Enable error reporting in development mode
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>