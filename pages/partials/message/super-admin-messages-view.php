<?php
require_once __DIR__ . '/../../../config/database.php';

$userId = $_SESSION['user_id'];
$replyToId = $_GET['reply_to_id'] ?? '';
$replyContext = null;

// Fetch Staff and Super Admins
$stmt = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE id != ?");
$stmt->execute([$userId]);
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If replying, fetch original message context
if (!empty($replyToId)) {
  $stmt = $pdo->prepare("
    SELECT m.subject, m.content, m.sender_id, CONCAT(u.first_name, ' ', u.last_name) AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.id = ?
  ");
  $stmt->execute([$replyToId]);
  $replyContext = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="bg-emerald-700 text-white p-5">
   <h2 class="text-lg font-semibold">Messages</h2>
</div>
<section class="bg-white p-6 rounded-b-lg shadow ">
<?php if ($replyContext): ?>
  <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4 rounded">
    <p class="text-sm text-blue-700">
      <strong>Replying to:</strong> <?= htmlspecialchars($replyContext['sender_name']) ?>
    </p>
    <?php if (!empty($replyContext['subject'])): ?>
      <p class="text-sm text-blue-600 italic">Subject: <?= htmlspecialchars($replyContext['subject']) ?></p>
    <?php endif; ?>
    <p class="text-sm text-gray-700 mt-1"><?= nl2br(htmlspecialchars($replyContext['content'])) ?></p>
  </div>
<?php endif; ?>

<form method="POST" action="/actions/send-message.php" class="space-y-4">
  <!-- Recipient Dropdown -->
  <select name="recipient_id" required class="w-full p-2 border rounded">
    <?php foreach ($recipients as $r): ?>
      <option value="<?= $r['id'] ?>"
        <?= ($replyContext && $r['id'] == $replyContext['sender_id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($r['full_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- Subject Field -->
  <input type="text" name="subject"
    value="<?= $replyContext ? 'Re: ' . htmlspecialchars($replyContext['subject']) : '' ?>"
    placeholder="Subject (optional)"
    class="w-full p-2 border rounded"
  />

  <!-- Message Body -->
  <textarea name="message" rows="4" required class="w-full p-2 border rounded" placeholder="Type your message..."></textarea>

  <!-- Reply-to Hidden Field -->
  <input type="hidden" name="reply_to_id" value="<?= htmlspecialchars($replyToId) ?>" />

  <!-- Submit Button -->
  <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Send</button>
</form>

<?php include __DIR__ . '/inbox-admin.php'; ?>
</section>