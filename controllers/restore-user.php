<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php'; // includes session_start()
require_once __DIR__ . '/../helpers/flash.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
  echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE users SET is_archived = 0 WHERE id = ?");
  $stmt->execute([$id]);

  setFlash('success', 'User restored successfully.');
  echo json_encode(['success' => true, 'message' => 'User restored successfully.']);
} catch (PDOException $e) {
  error_log("Restore failed: " . $e->getMessage());
  setFlash('error', 'Failed to restore user.');
  echo json_encode(['success' => false, 'message' => 'Failed to restore user.']);
}
?>