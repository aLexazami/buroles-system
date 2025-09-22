<?php
$userId = $_SESSION['user_id'];
$focusedId = $_GET['message_id'] ?? null;

// Fetch all messages for the list
$stmt = $pdo->prepare("
  SELECT m.id, m.subject, m.content, m.created_at, m.is_read,
         CONCAT(u.first_name, ' ', u.last_name) AS sender_name
  FROM messages m
  JOIN users u ON m.sender_id = u.id
  WHERE m.recipient_id = ?
  ORDER BY m.created_at DESC
  LIMIT 20
");
$stmt->execute([$userId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the focused message if one is selected
$focusedMessage = null;
if ($focusedId) {
  $focusStmt = $pdo->prepare("
    SELECT m.id, m.subject, m.content, m.created_at, m.is_read,
           CONCAT(u.first_name, ' ', u.last_name) AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.id = ? AND m.recipient_id = ?
    LIMIT 1
  ");
  $focusStmt->execute([$focusedId, $userId]);
  $focusedMessage = $focusStmt->fetch(PDO::FETCH_ASSOC);

  // Auto-mark as read
  if ($focusedMessage && !$focusedMessage['is_read']) {
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
?>

<div class="bg-emerald-700 text-white p-5">
  <h2 class="text-lg font-semibold">Messages</h2>
</div>

<section class="flex min-h-screen bg-white rounded-b-lg shadow">
  <?php include __DIR__ . '/../../../includes/side-nav-messages.php'; ?>

  <div class="flex-1 p-6 min-h-screen">
    <?php if ($focusedMessage): ?>
      <?php include __DIR__ . '/../../../pages/components/message-viewer.php'; ?>
    <?php else: ?>
      <?php include __DIR__ . '/../../../pages/components/message-list.php'; ?>
    <?php endif; ?>
  </div>
</section>