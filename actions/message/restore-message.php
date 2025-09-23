<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : null;

if ($userId && $messageId) {
  $stmt = $pdo->prepare("UPDATE messages SET deleted_by_recipient = 0 WHERE id = ? AND recipient_id = ?");
  $stmt->execute([$messageId, $userId]);
  setFlash('success', 'Message restored to inbox.');
} else {
  setFlash('error', 'Unable to restore message.');
}

header("Location: /pages/header/messages.php?view=trash");
exit;