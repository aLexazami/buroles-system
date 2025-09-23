<?php
require_once __DIR__ . '/../config/database.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
  echo json_encode(['messages' => 0, 'notifications' => 0]);
  exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0 AND deleted_by_recipient = 0");
$stmt->execute([$userId]);
$messages = $stmt->fetchColumn() ?? 0;

// Optional: if notifications table exists
$notifications = 0;

echo json_encode([
  'messages' => $messages,
  'notifications' => $notifications
]);