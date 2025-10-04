<?php
require_once __DIR__.'/../config/database.php';

header('Content-Type: application/json');

try {
  $stmt = $pdo->prepare("SELECT email, avatar_path FROM users WHERE is_archived = 0 ORDER BY email ASC");
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($results);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}