<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

$senderId = $_SESSION['user_id'] ?? null;

// Sanitize and validate input
$recipientId = $_POST['recipient_id'] ?? null;
$content     = trim($_POST['message'] ?? '');
$subject     = trim($_POST['subject'] ?? null);
$replyToId   = $_POST['reply_to_id'] ?? null;

if (!$senderId || !$recipientId || !$content) {
  setFlash('error', 'Missing required fields.');
  header('Location: /pages/header/messages.php');
  exit;
}

try {
  // Step 1: Insert into messages
  $stmt = $pdo->prepare("
    INSERT INTO messages (
      sender_id, recipient_id, subject, content, reply_to_id, created_at
    ) VALUES (?, ?, ?, ?, ?, NOW())
  ");
  $stmt->execute([
    $senderId,
    $recipientId,
    $subject ?: null,
    $content,
    $replyToId ?: null
  ]);

  $messageId = $pdo->lastInsertId();

  // Step 2: Insert sender and recipient states into message_user
  if ($senderId !== $recipientId) {
    $userStmt = $pdo->prepare("
      INSERT INTO message_user (message_id, user_id)
      VALUES (?, ?), (?, ?)
    ");
    $userStmt->execute([$messageId, $senderId, $messageId, $recipientId]);
  } else {
    $userStmt = $pdo->prepare("
      INSERT INTO message_user (message_id, user_id)
      VALUES (?, ?)
    ");
    $userStmt->execute([$messageId, $senderId]);
  }

  setFlash('success', 'Message sent successfully.');
} catch (PDOException $e) {
  error_log('Message send failed: ' . $e->getMessage());
  setFlash('error', 'Failed to send message. Please try again.');
}

header("Location: /pages/header/messages.php?view=compose");
exit;