<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$exclude = trim($_GET['exclude'] ?? '');

try {
  $sql = "SELECT first_name, middle_name, last_name, email, avatar_path FROM users WHERE is_archived = 0 AND role_id = 1";
  $params = [];

  if (!empty($exclude)) {
    $sql .= " AND LOWER(email) != LOWER(:exclude)";
    $params['exclude'] = strtolower($exclude);
  }

  $sql .= " ORDER BY email ASC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($results as &$user) {
    $user['full_name'] = trim(
      $user['first_name'] . ' ' .
      ($user['middle_name'] ?? '') . ' ' .
      $user['last_name']
    );
  }

  echo json_encode($results ?: []);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Internal server error']);
}