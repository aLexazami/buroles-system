<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : null;

if (!$userId || !$messageId) {
  setFlash('error', 'Unable to restore message.');
  header("Location: /pages/header/messages.php?view=trash");
  exit;
}

// Restore current user's copy
$restoreStmt = $pdo->prepare("
  UPDATE message_user SET is_deleted = 0
  WHERE message_id = ? AND user_id = ?
");
$restoreStmt->execute([$messageId, $userId]);

// Determine redirect view based on role
$roleStmt = $pdo->prepare("
  SELECT sender_id, recipient_id FROM messages WHERE id = ?
");
$roleStmt->execute([$messageId]);
$message = $roleStmt->fetch(PDO::FETCH_ASSOC);

if ($message) {
  $redirectView = ($message['sender_id'] == $userId) ? 'sent' : 'inbox';
  setFlash('success', 'Message restored.');
} else {
  $redirectView = 'inbox';
  setFlash('error', 'Message restored, but role could not be verified.');
}

header("Location: /pages/header/messages.php?view=trash");
exit;