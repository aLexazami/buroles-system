<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'];
$messageId = isset($_GET['message_id']) ? (int) $_GET['message_id'] : null;

if ($messageId) {
  $stmt = $pdo->prepare("
    UPDATE message_user SET is_read = 1
    WHERE message_id = ? AND user_id = ?
  ");
  $stmt->execute([$messageId, $userId]);
}

// Redirect to compose view with reply context
header("Location: /pages/header/messages.php?view=compose&reply_to_id=" . urlencode($messageId));
exit;