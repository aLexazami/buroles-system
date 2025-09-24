<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : null;
$context = $_POST['context'] ?? 'inbox';

if ($userId && $messageId) {
  $stmt = $pdo->prepare("
    UPDATE message_user SET is_deleted = 1
    WHERE message_id = ? AND user_id = ?
  ");
  $stmt->execute([$messageId, $userId]);

  $label = $context === 'sent' ? 'Sent message' : 'Message';
  setFlash('success', "{$label} moved to trash.");
} else {
  setFlash('error', 'Unable to delete message.');
}

$view = $context === 'sent' ? 'sent' : 'inbox';
header("Location: /pages/header/messages.php?view={$view}");
exit;