<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
$messageId = $_POST['message_id'] ?? null;

if ($userId && $messageId) {
  $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND (recipient_id = ? OR sender_id = ?)");
  $stmt->execute([$messageId, $userId, $userId]);
  setFlash('success', 'Message permanently deleted.');
} else {
  setFlash('error', 'Unable to delete message.');
}

header('Location: /pages/header/messages.php?view=trash');
exit;