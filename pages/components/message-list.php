    <!-- Message List View -->
    <div  class="flex-1 min-h-screen">
      <!-- Message List -->
      <div class="space-y-2">
        <?php foreach ($messages as $msg): ?>
          <a href="messages.php?view=inbox-admin&message_id=<?= $msg['id'] ?>" class="block">
            <?php include __DIR__ . '/message-card.php'; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>