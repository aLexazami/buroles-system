<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Ensure only super admins can unlock accounts
if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 2) {
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['user_id']) ? (int)$input['user_id'] : 0;

if (!$userId) {
  echo json_encode(['success' => false, 'error' => 'Missing user ID']);
  exit;
}

// Unlock logic
try {
  $stmt = $pdo->prepare("UPDATE users SET is_locked = 0 WHERE id = :id");
  $stmt->execute(['id' => $userId]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'error' => 'Database error']);
}