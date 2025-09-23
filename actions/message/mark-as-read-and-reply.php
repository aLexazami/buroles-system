<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'];
$messageId = $_GET['message_id'] ?? null;

if ($messageId) {
  $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND recipient_id = ?");
  $stmt->execute([$messageId, $userId]);
}

// Redirect to compose view with reply context
header("Location: /pages/header/messages.php?view=compose&reply_to_id=" . urlencode($messageId));
exit;