<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php'; // Adjust path if needed

try {
    $stmt = $pdo->query("SELECT NOW() AS current_time");
    $result = $stmt->fetch();
    echo "<h2>✅ Database connection successful!</h2>";
    echo "<p>Server time: <strong>{$result['current_time']}</strong></p>";
} catch (PDOException $e) {
    echo "<h2>❌ Connection failed</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
