<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'];
$roleId = $_SESSION['role_id'];
$view = $_GET['view'] ?? 'inbox';
$focusedId = $_GET['message_id'] ?? null;

$context = match ($view) {
  'sent' => 'sent',
  'trash' => 'trash',
  'compose' => 'compose',
  default => 'inbox'
};

$isInbox = $context === 'inbox';
$isSent = $context === 'sent';
$isTrash = $context === 'trash';
$isCompose = $context === 'compose';

$messages = [];
$focusedMessage = null;

if ($isCompose) return;

$directionColumn = ($isInbox || $isTrash) ? 'recipient_id' : 'sender_id';
$nameColumn = ($isInbox || $isTrash) ? 'sender_name' : 'recipient_name';
$nameJoin = ($isInbox || $isTrash) ? 'm.sender_id = u.id' : 'm.recipient_id = u.id';

$whereClause = match ($context) {
  'trash' => "(m.recipient_id = ? AND m.deleted_by_recipient = 1) OR (m.sender_id = ? AND m.deleted_by_sender = 1)",
  'inbox' => "m.recipient_id = ? AND m.deleted_by_recipient = 0",
  'sent' => "m.sender_id = ? AND m.deleted_by_sender = 0",
  default => "m.$directionColumn = ?"
};

$stmt = $pdo->prepare("
  SELECT m.id, m.subject, m.content, m.created_at, m.is_read,
         m.sender_id, m.recipient_id,
         CONCAT(u.first_name, ' ', u.last_name) AS $nameColumn
  FROM messages m
  JOIN users u ON $nameJoin
  WHERE $whereClause
  ORDER BY m.created_at DESC
  LIMIT 20
");

$params = $isTrash ? [$userId, $userId] : [$userId];
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($focusedId) {
  if ($isTrash) {
    $focusStmt = $pdo->prepare("
      SELECT m.id, m.subject, m.content, m.created_at, m.is_read,
             CONCAT(u.first_name, ' ', u.last_name) AS sender_name
      FROM messages m
      JOIN users u ON m.sender_id = u.id
      WHERE m.id = ? AND (m.recipient_id = ? OR m.sender_id = ?)
      LIMIT 1
    ");
    $focusStmt->execute([$focusedId, $userId, $userId]);
  } else {
    $focusStmt = $pdo->prepare("
      SELECT m.id, m.subject, m.content, m.created_at, m.is_read,
             CONCAT(u.first_name, ' ', u.last_name) AS $nameColumn
      FROM messages m
      JOIN users u ON $nameJoin
      WHERE m.id = ? AND m.$directionColumn = ?
      LIMIT 1
    ");
    $focusStmt->execute([$focusedId, $userId]);
  }

  $focusedMessage = $focusStmt->fetch(PDO::FETCH_ASSOC);

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