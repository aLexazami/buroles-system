<?php
require_once __DIR__.'/../auth/session.php';
require_once __DIR__.'/../config/database.php';

$userId = $_SESSION['user_id'];
$roleId = $_SESSION['role_id'];
$view = $_GET['view'] ?? 'inbox';
$focusedId = $_GET['message_id'] ?? null;

$context = $view === 'sent' ? 'sent' : ($view === 'trash' ? 'trash' : ($view === 'compose' ? 'compose' : 'inbox'));
$isInbox = $context === 'inbox';
$isSent = $context === 'sent';
$isTrash = $context === 'trash';
$isCompose = $context === 'compose';

$messages = [];
$focusedMessage = null;

if ($isCompose) {
  return;
}

$directionColumn = $isInbox ? 'recipient_id' : 'sender_id';
$nameColumn = $isInbox ? 'sender_name' : 'recipient_name';
$nameJoin = $isInbox ? 'm.sender_id = u.id' : 'm.recipient_id = u.id';

// Fetch messages
$stmt = $pdo->prepare("
  SELECT m.id, m.subject, m.content, m.created_at, m.is_read,
         CONCAT(u.first_name, ' ', u.last_name) AS $nameColumn
  FROM messages m
  JOIN users u ON $nameJoin
  WHERE m.$directionColumn = ?
  ORDER BY m.created_at DESC
  LIMIT 20
");
$stmt->execute([$userId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch focused message
if ($focusedId) {
  $focusStmt = $pdo->prepare("
    SELECT m.id, m.subject, m.content, m.created_at, m.is_read,
           CONCAT(u.first_name, ' ', u.last_name) AS $nameColumn
    FROM messages m
    JOIN users u ON $nameJoin
    WHERE m.id = ? AND m.$directionColumn = ?
    LIMIT 1
  ");
  $focusStmt->execute([$focusedId, $userId]);
  $focusedMessage = $focusStmt->fetch(PDO::FETCH_ASSOC);

  // Auto-mark as read (inbox only)
  if ($isInbox && $focusedMessage && !$focusedMessage['is_read']) {
    $markStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND recipient_id = ?");
    $markStmt->execute([$focusedId, $userId]);
    $focusedMessage['is_read'] = 1;
    foreach ($messages as &$msg) {
      if ($msg['id'] == $focusedId) {
        $msg['is_read'] = 1;
        break;
      }
    }
  }
}