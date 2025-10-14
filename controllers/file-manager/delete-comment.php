<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? null;

if (!$commentId) {
  echo json_encode(['success' => false, 'error' => 'Missing comment ID']);
  exit;
}

try {
  // 🗑️ Permanently delete comment (only if owned by user)
  $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
  $success = $stmt->execute([$commentId, $userId]);

  echo json_encode([
    'success' => $success,
    'message' => $success ? 'Comment permanently deleted' : 'Failed to delete comment'
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'error' => 'Server error: ' . $e->getMessage()
  ]);
}