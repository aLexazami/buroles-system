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

// View filter
$filterClause = match ($context) {
  'trash' => 'mu.is_deleted = 1',
  default => 'mu.is_deleted = 0'
};

// Role filter
$roleClause = match ($context) {
  'sent' => 'mu.user_id = ? AND m.sender_id = ?',
  'inbox' => 'mu.user_id = ? AND m.recipient_id = ?',
  default => 'mu.user_id = ?'
};

// Name column
$nameColumn = $isSent ? 'recipient_name' : 'sender_name';
$nameJoin = $isSent ? 'm.recipient_id = u.id' : 'm.sender_id = u.id';

// Prepare query (no GROUP BY)
$stmt = $pdo->prepare("
  SELECT m.id, m.subject, m.content, m.created_at,
         mu.is_read, mu.is_deleted,
         m.sender_id, m.recipient_id,
         CONCAT(u.first_name, ' ', u.last_name) AS $nameColumn
  FROM message_user mu
  JOIN messages m ON mu.message_id = m.id
  JOIN users u ON $nameJoin
  WHERE $roleClause AND $filterClause
  ORDER BY m.created_at DESC
  LIMIT 20
");

// Bind parameters
$params = match ($context) {
  'sent' => [$userId, $userId],
  'inbox' => [$userId, $userId],
  'trash' => [$userId],
  default => [$userId]
};

$stmt->execute($params);
$rawMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Deduplicate by message ID
$uniqueMessages = [];
foreach ($rawMessages as $msg) {
  $uniqueMessages[$msg['id']] = $msg;
}
$messages = array_values($uniqueMessages);

// Focused message
if ($focusedId) {
  $focusClause = $isInbox
    ? 'm.id = ? AND mu.user_id = ? AND m.recipient_id = ?'
    : 'm.id = ? AND mu.user_id = ?';

  $focusStmt = $pdo->prepare("
    SELECT m.id, m.subject, m.content, m.created_at,
           mu.is_read, mu.is_deleted,
           CONCAT(u.first_name, ' ', u.last_name) AS $nameColumn
    FROM message_user mu
    JOIN messages m ON mu.message_id = m.id
    JOIN users u ON $nameJoin
    WHERE $focusClause
    LIMIT 1
  ");

  $focusParams = $isInbox ? [$focusedId, $userId, $userId] : [$focusedId, $userId];
  $focusStmt->execute($focusParams);
  $focusedMessage = $focusStmt->fetch(PDO::FETCH_ASSOC);

  // Mark as read
  if ($isInbox && $focusedMessage && !$focusedMessage['is_read']) {
    $markStmt = $pdo->prepare("
      UPDATE message_user SET is_read = 1 WHERE message_id = ? AND user_id = ?
    ");
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