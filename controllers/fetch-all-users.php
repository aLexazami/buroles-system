<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$exclude = trim($_GET['exclude'] ?? '');

try {
  $sql = "SELECT email, avatar_path FROM users WHERE is_archived = 0";
  $params = [];

  if (!empty($exclude)) {
    $sql .= " AND LOWER(email) != LOWER(:exclude)";
    $params['exclude'] = strtolower($exclude);
  }

  $sql .= " ORDER BY email ASC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($results ?: []);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Internal server error']);
}