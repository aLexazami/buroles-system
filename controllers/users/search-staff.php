<?php
require_once __DIR__ . '/../../config/database.php';

$query = trim($_GET['query'] ?? '');
$exclude = trim($_GET['exclude'] ?? '');

if (strlen($query) < 1) {
  echo json_encode([]);
  exit;
}

try {
  $sql = "SELECT email
          FROM users
          WHERE is_archived = 0
            AND LOWER(email) LIKE LOWER(:likeQuery)";

  if (!empty($exclude)) {
    $sql .= " AND LOWER(email) != LOWER(:exclude)";
  }

  $sql .= " ORDER BY email ASC LIMIT 10";

  $stmt = $pdo->prepare($sql);

  $params = ['likeQuery' => "%$query%"];
  if (!empty($exclude)) {
    $params['exclude'] = $exclude;
  }

  $stmt->execute($params);
  $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

  echo json_encode($results);
} catch (Exception $e) {
  error_log("Email search error: " . $e->getMessage());
  echo json_encode([]);
}