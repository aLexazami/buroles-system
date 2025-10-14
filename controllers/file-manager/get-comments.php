<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

$fileId = $_GET['file_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$fileId) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing file ID or session']);
  exit;
}

$stmt = $pdo->prepare("
  SELECT c.content, c.created_at, u.first_name, u.last_name
  FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE c.file_id = ?
  ORDER BY c.created_at ASC
");
$stmt->execute([$fileId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($comments);