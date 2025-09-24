<?php
require_once __DIR__ . '/../config/database.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;

$unreadMessages = 0;
$unreadNotifs = 0;

if ($userId) {
  // Updated unread message count
  $stmt = $pdo->prepare("
    SELECT COUNT(*) FROM message_user mu
    JOIN messages m ON mu.message_id = m.id
    WHERE mu.user_id = ? AND mu.is_read = 0 AND mu.is_deleted = 0 AND m.recipient_id = ?
  ");
  $stmt->execute([$userId, $userId]);
  $unreadMessages = $stmt->fetchColumn() ?? 0;

  // Optional: update unreadNotifs logic if you have a notifications table
}

echo json_encode([
  'messages' => $unreadMessages,
  'notifications' => $unreadNotifs
]);