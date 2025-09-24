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

// Fetch sender and recipient to determine role
$stmt = $pdo->prepare("SELECT sender_id, recipient_id FROM messages WHERE id = ?");
$stmt->execute([$messageId]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
  setFlash('error', 'Message not found.');
  header("Location: /pages/header/messages.php?view=trash");
  exit;
}

// Restore based on user role
if ($message['recipient_id'] == $userId) {
  $restoreStmt = $pdo->prepare("UPDATE messages SET deleted_by_recipient = 0 WHERE id = ?");
  $restoreStmt->execute([$messageId]);
  setFlash('success', 'Message restored to inbox.');
  $redirectView = 'inbox';
} elseif ($message['sender_id'] == $userId) {
  $restoreStmt = $pdo->prepare("UPDATE messages SET deleted_by_sender = 0 WHERE id = ?");
  $restoreStmt->execute([$messageId]);
  setFlash('success', 'Message restored to sent messages.');
  $redirectView = 'sent';
} else {
  setFlash('error', 'You are not authorized to restore this message.');
  $redirectView = 'trash';
}

header("Location: /pages/header/messages.php?view={$redirectView}");
exit;