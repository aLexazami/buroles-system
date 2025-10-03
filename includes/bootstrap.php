<?php
// ✅ Load Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

$dotenvPath = __DIR__ . '/../';

// ✅ Load base .env first to get APP_ENV
$baseEnv = Dotenv\Dotenv::createImmutable($dotenvPath, '.env');
$baseEnv->load();

// ✅ Decide which env file to load based on APP_ENV
$envFile = match ($_ENV['APP_ENV'] ?? 'production') {
    'local' => '.env.local',
    'production' => '.env.production',
    default => '.env.production',
};

$envFullPath = $dotenvPath . $envFile;
if (file_exists($envFullPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath, $envFile);
    $dotenv->load();
} else {
    throw new RuntimeException("Missing $envFile file at $dotenvPath");
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