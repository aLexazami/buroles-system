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
  SELECT c.id AS comment_id, c.file_id, c.content, c.created_at, u.first_name, u.last_name, u.avatar_path, f.name AS file_name, f.type, f.owner_id
FROM comments c
JOIN users u ON c.user_id = u.id
JOIN files f ON c.file_id = f.id
WHERE c.user_id = :userId
ORDER BY c.created_at DESC
");
$stmt->execute([$userId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($comments);