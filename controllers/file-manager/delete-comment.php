<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$fileId = $data['file_id'] ?? null;

if (!$fileId) {
  echo json_encode(['success' => false, 'error' => 'Missing file ID']);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM comments WHERE file_id = ? AND user_id = ?");
$success = $stmt->execute([$fileId, $userId]);

echo json_encode(['success' => $success]);