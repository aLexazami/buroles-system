<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : null;
$context = $_POST['context'] === 'sent' ? 'sent' : 'inbox';

if ($userId && $messageId) {
  if ($context === 'sent') {
    // Optional: hard delete for sent messages
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->execute([$messageId, $userId]);
    setFlash('success', 'Sent message permanently deleted.');
  } else {
    // Soft delete: move to trash
    $stmt = $pdo->prepare("UPDATE messages SET deleted_by_recipient = 1 WHERE id = ? AND recipient_id = ?");
    $stmt->execute([$messageId, $userId]);
    setFlash('success', 'Message moved to trash.');
  }
} else {
  setFlash('error', 'Unable to delete message.');
}

// Redirect to appropriate view
$view = $context === 'sent' ? 'sent' : 'inbox';
header("Location: /pages/header/messages.php?view={$view}");
exit;