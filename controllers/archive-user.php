<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
  echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE users SET is_archived = 1 WHERE id = ?");
  $stmt->execute([$id]);

  setFlash('success', 'User archived successfully.');
  echo json_encode(['success' => true, 'message' => 'User archived successfully.']);
} catch (PDOException $e) {
  error_log("Archive failed: " . $e->getMessage());
  setFlash('error', 'Failed to archive user.');
  echo json_encode(['success' => false, 'message' => 'Failed to archive user.']);
}
?>