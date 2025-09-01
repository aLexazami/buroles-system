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
  $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
  $stmt->execute([$id]);

  // Optional: set flash for non-AJAX fallback or logging
  setFlash('success', 'User deleted successfully.');

  echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
} catch (PDOException $e) {
  error_log("Delete failed: " . $e->getMessage());

  // Optional: set flash for fallback
  setFlash('error', 'Failed to delete user.');

  echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
}
?>