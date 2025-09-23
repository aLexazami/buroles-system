<!-- Message List View -->
<div class="flex-1 min-h-screen">
  <div class="space-y-2">
    <?php if (empty($messages)): ?>
      <div class="text-center text-gray-500 mt-10">
        <p class="text-lg font-semibold">No messages found in <?= htmlspecialchars($context) ?>.</p>
        <p class="text-sm">
          <?= $context === 'sent'
            ? 'You haven’t sent any messages yet.'
            : 'Your inbox is currently empty.' ?>
        </p>

        <?php if ($context === 'inbox'): ?>
          <a href="messages.php?view=compose" class="inline-block mt-4 px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-500 transition">
            Start a conversation
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($messages as $msg): ?>
          <a href="messages.php?view=<?= $context ?>&message_id=<?= $msg['id'] ?>" class="block">
            <?php include __DIR__ . '/message-card.php'; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>