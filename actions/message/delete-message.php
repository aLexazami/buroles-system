<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role_id'] ?? null;
$messageId = isset($_POST['message_id']) ? (int) $_POST['message_id'] : null;
$context = $_POST['context'] === 'sent' ? 'sent' : 'inbox';

if ($userId && $messageId) {
  if ($context === 'sent') {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
  } else {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND recipient_id = ?");
  }

  $stmt->execute([$messageId, $userId]);
  setFlash('success', 'Message deleted successfully.');
} else {
  setFlash('error', 'Unable to delete message.');
}

// Redirect to appropriate view
$view = $context === 'sent' ? 'sent' : 'inbox';

switch ($role) {
  case '1':
    header("Location: /pages/header/messages.php?view={$view}-staff");
    break;
  case '2':
    header("Location: /pages/header/messages.php?view={$view}-admin");
    break;
  case '99':
    header("Location: /pages/header/messages.php?view={$view}-super-admin");
    break;
  default:
    header("Location: /pages/header/messages.php");
    break;
}

exit;