<?php
$userId = $_SESSION['user_id'] ?? null;
$replyToId = $_GET['reply_to_id'] ?? '';
$replyContext = null;

// Role labels for display
$roleLabels = [
  1 => 'Staff',
  2 => 'Admin',
  99 => 'Super Admin'
];

// Fetch all users except the sender, including role_id
$stmt = $pdo->prepare("
  SELECT id, role_id, CONCAT(first_name, ' ', last_name) AS full_name
  FROM users
  WHERE id != ?
");
$stmt->execute([$userId]);
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sort alphabetically by full name
usort($recipients, fn($a, $b) => strcasecmp($a['full_name'], $b['full_name']));

// Group recipients by role
$groupedRecipients = [];
foreach ($recipients as $r) {
  $roleId = $r['role_id'];
  $groupedRecipients[$roleId][] = $r;
}

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

  // Crop long reply content
  if ($replyContext && isset($replyContext['content'])) {
    $replyPreview = mb_substr($replyContext['content'], 0, 100);
    $isLongReply = mb_strlen($replyContext['content']) > 100;
  }
  $preselectedRecipientId = $replyContext['sender_id'] ?? null;
}
?>

<div class="bg-emerald-700 text-white p-5">
  <h2 class="text-lg font-semibold">Messages</h2>
</div>

<section class="flex bg-white rounded-b-lg shadow">
  <!-- Sidebar Navigation -->
  <?php include __DIR__ . '/../../../includes/side-nav-messages.php'; ?>

  <!-- Main Form Area -->
  <div class="flex-1 p-6 min-h-screen">
    <?php if ($replyContext): ?>
      <div class="bg-emerald-50 p-4 border-x-4 border-emerald-700 mb-4 rounded">
        <?php if (!empty($replyContext['sender_name'])): ?>
          <p class="text-sm text-emerald-700">
            <strong>Replying to:</strong> <?= htmlspecialchars($replyContext['sender_name'] ?? '') ?>
          </p>
        <?php endif; ?>

        <?php if (!empty($replyContext['subject'])): ?>
          <p class="text-sm text-emerald-600 italic">
            Subject: <?= htmlspecialchars($replyContext['subject'] ?? '') ?>
          </p>
        <?php endif; ?>

        <?php if (!empty($replyContext['content'])): ?>
          <p class="text-sm text-gray-700 mt-1">
            <?= nl2br(htmlspecialchars(
              mb_strlen($replyContext['content'] ?? '') > 100
                ? mb_substr($replyContext['content'], 0, 100) . '…'
                : $replyContext['content'] ?? ''
            )) ?>
          </p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/actions/message/send-message.php" class="space-y-4">
      <?php if (empty($recipients)): ?>
        <p class="text-red-600 text-sm">No available recipients.</p>
      <?php else: ?>
        <div class="relative w-full" id="recipient-dropdown">
          <button type="button" id="dropdown-toggle" aria-haspopup="listbox" aria-expanded="false" class="w-full p-2 border rounded bg-white text-left flex justify-between items-center">
            <span id="selected-recipient" class="flex items-center space-x-2 text-sm font-medium text-gray-800">Select recipient</span>
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div id="dropdown-menu" class="absolute z-10 mt-1 w-full bg-white border rounded shadow-lg hidden max-h-60 overflow-y-auto">
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
        value="<?= htmlspecialchars($replyContext['subject'] ?? '') ?>"
        placeholder="Subject (optional)"
        class="w-full p-2 border rounded" />

      <textarea name="message" rows="12" required class="w-full p-2 border rounded resize-none" placeholder="Type your message..."></textarea>

      <input type="hidden" name="reply_to_id" value="<?= htmlspecialchars($replyToId) ?>" />

      <div class="sticky bottom-0 bg-white p-4 flex justify-end">
        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-emerald-500">
          Send
        </button>
      </div>
    </form>
  </div>
</section>