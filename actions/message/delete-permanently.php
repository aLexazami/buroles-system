<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
$messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : null;

if ($userId && $messageId) {
  // Step 1: Delete current user's message_user row
  $deleteStmt = $pdo->prepare("
    DELETE FROM message_user WHERE message_id = ? AND user_id = ?
  ");
  $deleteStmt->execute([$messageId, $userId]);

  // Step 2: Check if any message_user rows remain
  $checkStmt = $pdo->prepare("
    SELECT COUNT(*) FROM message_user WHERE message_id = ?
  ");
  $checkStmt->execute([$messageId]);
  $remaining = $checkStmt->fetchColumn();

  // Step 3: If none remain, delete the message itself
  if ($remaining == 0) {
    $cleanupStmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $cleanupStmt->execute([$messageId]);
  }

  setFlash('success', 'Message permanently deleted.');
} else {
  setFlash('error', 'Unable to delete message.');
}

header('Location: /pages/header/messages.php?view=trash');
exit;