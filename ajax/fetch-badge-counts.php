<?php
require_once __DIR__ . '/../config/database.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
$originalRoleId = $_SESSION['original_role_id'] ?? null;

$response = [
  'messages' => 0,
  'notifications' => 0
];

if ($userId) {
  // 🔔 Unread messages
  $msgStmt = $pdo->prepare("
    SELECT COUNT(*) FROM message_user mu
    JOIN messages m ON mu.message_id = m.id
    WHERE mu.user_id = ? AND mu.is_read = 0 AND mu.is_deleted = 0 AND m.recipient_id = ?
  ");
  $msgStmt->execute([$userId, $userId]);
  $response['messages'] = $msgStmt->fetchColumn() ?? 0;

  // 🔔 Unread notifications (user or original role)
  $notifStmt = $pdo->prepare("
    SELECT COUNT(*) FROM notifications
    WHERE (user_id = :userId OR role_id = :roleId) AND is_read = 0
  ");
  $notifStmt->execute([
    'userId' => $userId,
    'roleId' => $originalRoleId
  ]);
  $response['notifications'] = $notifStmt->fetchColumn() ?? 0;
}

header('Content-Type: application/json');
echo json_encode($response);