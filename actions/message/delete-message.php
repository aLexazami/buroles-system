<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role_id'] ?? null;
$messageId = $_POST['message_id'] ?? null;

if ($userId && $messageId) {
  $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND recipient_id = ?");
  $stmt->execute([$messageId, $userId]);

  setFlash('success', 'Message deleted successfully.');
} else {
  setFlash('error', 'Unable to delete message.');
}

// Redirect to messages.php with appropriate view
switch ($role) {
  case '1':
    header('Location: /pages/header/messages.php?view=inbox-staff');
    break;
  case '2':
    header('Location: /pages/header/messages.php?view=inbox-admin');
    break;
  case '99':
    header('Location: /pages/header/messages.php?view=inbox-super-admin');
    break;
  default:
    header('Location: /pages/header/messages.php'); // fallback
    break;
}

exit;