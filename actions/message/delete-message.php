<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : null;
$context = $_POST['context'] ?? 'inbox';

if ($userId && $messageId) {
  if ($context === 'sent') {
    $stmt = $pdo->prepare("UPDATE messages SET deleted_by_sender = 1 WHERE id = ? AND sender_id = ?");
    $stmt->execute([$messageId, $userId]);
    setFlash('success', 'Sent message moved to trash.');
  } else {
    $stmt = $pdo->prepare("UPDATE messages SET deleted_by_recipient = 1 WHERE id = ? AND recipient_id = ?");
    $stmt->execute([$messageId, $userId]);
    setFlash('success', 'Message moved to trash.');
  }
} else {
  setFlash('error', 'Unable to delete message.');
}

$view = $context === 'sent' ? 'sent' : 'inbox';
header("Location: /pages/header/messages.php?view={$view}");
exit;