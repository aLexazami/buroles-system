<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$stmt = $pdo->prepare("
  SELECT c.content, c.created_at, f.name AS file_name, f.id AS file_id, u.first_name, u.last_name
  FROM comments c
  JOIN files f ON c.file_id = f.id
  JOIN users u ON c.user_id = u.id
  WHERE c.user_id = ?
  ORDER BY c.created_at DESC
");
$stmt->execute([$userId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($comments);