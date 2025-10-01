<?php
$userId = $_SESSION['user_id'] ?? null;
$replyToId = $_GET['reply_to_id'] ?? '';
$replyContext = null;
$context = 'compose';

// Role labels
$roleLabels = [
  1 => 'Staff',
  2 => 'Admin',
  99 => 'Super Admin'
];

// Fetch all users except sender
$stmt = $pdo->prepare("
  SELECT id, role_id, CONCAT(first_name, ' ', last_name) AS full_name
  FROM users
  WHERE id != ?
");
$stmt->execute([$userId]);
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sort and group
usort($recipients, fn($a, $b) => strcasecmp($a['full_name'], $b['full_name']));
$groupedRecipients = [];
foreach ($recipients as $r) {
  $groupedRecipients[$r['role_id']][] = $r;
}

// If replying, validate ownership
$preselectedRecipientId = null;
$replySubject = ''; // Always define early

if (!empty($replyToId)) {
  $stmt = $pdo->prepare("
    SELECT m.subject, m.content, m.sender_id,
           CONCAT(u.first_name, ' ', u.last_name) AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.id = ? AND EXISTS (
      SELECT 1 FROM message_user mu
      WHERE mu.message_id = m.id AND mu.user_id = ?
    )
  ");
  $stmt->execute([$replyToId, $userId]);
  $replyContext = $stmt->fetch(PDO::FETCH_ASSOC);

  // Prevent self-reply
  if ($replyContext && $replyContext['sender_id'] === $userId) {
    $replyContext = null;
  }

  if ($replyContext) {
    $preselectedRecipientId = $replyContext['sender_id'] ?? null;

    // Normalize subject safely
    $original = trim($replyContext['subject'] ?? '');
    $replySubject = $original !== ''
      ? (preg_match('/^Re:/i', $original) ? $original : 'Re: ' . $original)
      : 'Re:';
  }
}
?>

<div class="bg-emerald-700 text-white px-4 py-5 sm:px-6 sm:py-6">
  <h2 class="text-lg font-semibold"><?= ucfirst($context) ?> Messages</h2>
</div>
<section class="flex flex-col md:flex-row bg-white rounded-b-lg shadow">
  <?php include __DIR__ . '/../../../includes/side-nav-messages.php'; ?>

  <div class="flex-1 p-4 sm:p-6 min-h-screen">
    <?php if ($replyContext): ?>
      <div class="bg-emerald-50 p-4 border-x-4 border-emerald-700 mb-4 rounded">
        <?php if (!empty($replyContext['sender_name'])): ?>
          <p class="text-sm text-emerald-700">
            <strong>Replying to:</strong> <?= htmlspecialchars($replyContext['sender_name']) ?>
          </p>
        <?php endif; ?>
        <?php if (!empty($replyContext['subject'])): ?>
          <p class="text-sm text-emerald-600 italic">
            Subject: <?= htmlspecialchars($replySubject) ?>
          </p>
        <?php endif; ?>
        <?php if (!empty($replyContext['content'])): ?>
          <p class="text-sm text-gray-700 mt-1">
            <?= nl2br(htmlspecialchars(mb_strlen($replyContext['content']) > 100
              ? mb_substr($replyContext['content'], 0, 100) . '…'
              : $replyContext['content'])) ?>
          </p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/actions/message/send-message.php" class="space-y-4">
      <?php if (empty($recipients)): ?>
        <p class="text-red-600 text-sm">No available recipients.</p>
      <?php else: ?>
        <div class="relative w-full" id="recipient-dropdown">
          <button type="button" id="dropdown-toggle" class="w-full p-2 border rounded bg-white text-left flex justify-between items-center">
            <span id="selected-recipient" class="flex items-center space-x-2 text-sm font-medium text-gray-800">
              <?= isset($preselectedRecipientId)
                ? htmlspecialchars($replyContext['sender_name'] ?? 'Select recipient')
                : 'Select recipient' ?>
            </span>
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div id="dropdown-menu" class="absolute z-10 mt-1 w-full bg-white border rounded shadow-lg hidden max-h-60 overflow-y-auto text-sm">
            <?php foreach ($roleLabels as $roleId => $roleName): ?>
              <?php if (!empty($groupedRecipients[$roleId])): ?>
                <div class="px-2 py-1 bg-gray-100 text-xs font-semibold text-gray-600 uppercase"><?= htmlspecialchars($roleName) ?></div>
                <?php foreach ($groupedRecipients[$roleId] as $r): ?>
                  <div class="recipient-option px-2 py-1 hover:bg-emerald-50 cursor-pointer flex space-x-2 items-center" data-id="<?= $r['id'] ?>">
                    <span class="bg-emerald-800 py-1 rounded px-2 text-xs font-semibold text-white"><?= htmlspecialchars($roleName) ?></span>
                    <span class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($r['full_name']) ?></span>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <input type="hidden" name="recipient_id" id="recipient-id" value="<?= htmlspecialchars($preselectedRecipientId ?? '') ?>" required>
        </div>
      <?php endif; ?>

      <input type="text" name="subject"
        value="<?= htmlspecialchars($replySubject) ?>"
        placeholder="Subject (optional)"
        class="w-full p-2 border rounded" />

      <textarea name="message" rows="12" required class="w-full p-2 border rounded resize-none" placeholder="Type your message..."></textarea>

      <input type="hidden" name="reply_to_id" value="<?= htmlspecialchars($replyToId) ?>" />

      <div class="sticky bottom-0 bg-white px-4 py-3 sm:px-6 flex flex-col sm:flex-row justify-end gap-2 text-center">
        <a href="/pages/header/messages.php?view=inbox" class="px-4 py-2  bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
          Cancel
        </a>
        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-emerald-500">
          Send
        </button>
      </div>
    </form>
  </div>
</section>