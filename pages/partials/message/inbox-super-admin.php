<?php
require_once __DIR__ . '/../../../config/database.php';

$userId = $_SESSION['user_id'];

// Fetch latest 20 messages sent to this admin
$stmt = $pdo->prepare("
  SELECT
    m.id,
    m.subject,
    m.content,
    m.created_at,
    m.is_read,
    CONCAT(u.first_name, ' ', u.last_name) AS sender_name
  FROM messages m
  JOIN users u ON m.sender_id = u.id
  WHERE m.recipient_id = ?
  ORDER BY m.created_at DESC
  LIMIT 20
");
$stmt->execute([$userId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-emerald-700 text-white p-5">
  <h2 class="text-lg font-semibold">Messages</h2>
</div>
<section class="flex bg-white rounded-b-lg shadow">
  <!-- Sidebar Navigation -->
  <?php include __DIR__ . '/../../../includes/side-nav-messages.php'; ?>

  <!-- Main Form Area -->
  <div class="flex-1 p-6 min-h-screen">
    <?php if (count($messages) === 0): ?>
      <p class="text-gray-600">No messages yet.</p>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($messages as $msg): ?>
          <div class="bg-white p-4 rounded shadow border-l-4 <?= $msg['is_read'] ? 'border-gray-300' : 'border-blue-500' ?>">
            <div class="text-sm text-gray-500 mb-1">
              <strong>From:</strong> <?= htmlspecialchars($msg['sender_name']) ?> |
              <strong>Date:</strong> <?= date('M d, Y H:i', strtotime($msg['created_at'])) ?>
            </div>

            <?php if (!empty($msg['subject'])): ?>
              <div class="font-semibold text-blue-700 mb-1">
                <?= htmlspecialchars($msg['subject']) ?>
              </div>
            <?php endif; ?>

            <p class="text-gray-800 whitespace-pre-line"><?= htmlspecialchars($msg['content']) ?></p>

            <!-- Reply Link -->
            <div class="mt-2">
              <a href="messages.php?reply_to_id=<?= $msg['id'] ?>" class="text-purple-600 underline text-sm">Reply</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>