<?php
<<<<<<< HEAD
require_once __DIR__ . '/../includes/bootstrap.php'; // Loads Dotenv and Composer autoload

class Database {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        // Validate required environment variables
        $required = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($required as $key) {
            if (empty($_ENV[$key])) {
                throw new RuntimeException("Missing required environment variable: $key");
            }
        }

        // Build DSN
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'],
            $_ENV['DB_DATABASE']
        );

        // Lazy connection: reuse PDO instance
        if (self::$instance === null) {
            try {
                self::$instance = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'] ?? '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                $message = "Database connection failed: " . $e->getMessage();

                if ($_ENV['APP_ENV'] === 'local') {
                    die("❌ " . $message);
                } else {
                    error_log(date('[Y-m-d H:i:s]') . " " . $message);
                    die("Database connection failed. Please contact the administrator.");
                }
            }
        }

        return self::$instance;
=======
require_once __DIR__ . '/../includes/bootstrap.php'; // Loads Dotenv and autoload

class Database {
    public static function connect(): PDO {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_DATABASE']
            );

            return new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Environment-aware error handling
            if ($_ENV['APP_ENV'] === 'local') {
                die("Database connection failed: " . $e->getMessage());
            } else {
                error_log("Database connection error: " . $e->getMessage());
                die("Database connection failed. Please contact the administrator.");
            }
        }
>>>>>>> 6daf51bd0c038bd9f6b95409d26672fc23d288f9
    }
}

// Create a PDO instance for use in controllers
<<<<<<< HEAD
$pdo = Database::connect();
=======
$pdo = Database::connect();
?>
>>>>>>> 6daf51bd0c038bd9f6b95409d26672fc23d288f9
