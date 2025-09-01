<?php
// ✅ Load Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// ✅ Load environment variables from .env
$dotenvPath = __DIR__ . '/../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
} else {
    throw new RuntimeException("Missing .env file at $dotenvPath");
}

// ✅ Define global constants
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_NAME', $_ENV['APP_NAME'] ?? 'BurolES');
define('DEBUG_MODE', APP_ENV === 'local');

// ✅ Configure error reporting
ini_set('display_errors', DEBUG_MODE ? '1' : '0');
error_reporting(DEBUG_MODE ? E_ALL : 0);

// ✅ Optional: Set default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');