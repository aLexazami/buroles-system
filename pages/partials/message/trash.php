<?php
require_once __DIR__ . '/../../../helpers/message-router.php';
?>

<div class="bg-emerald-700 text-white p-5">
  <h2 class="text-lg font-semibold"><?= ucfirst($context) ?> Messages</h2>
</div>

<section class="flex min-h-screen bg-white rounded-b-lg shadow">
  <?php include __DIR__ . '/../../../includes/side-nav-messages.php'; ?>

  <div class="flex-1 p-6 min-h-screen">
    <?php if ($focusedMessage): ?>
      <?php include __DIR__ . '/../../../pages/components/message-viewer.php'; ?>
    <?php elseif (empty($messages)): ?>
      <div class="text-center text-gray-500 mt-10">
        <p class="text-lg font-semibold">No messages in Trash.</p>
        <p class="text-sm">You haven’t deleted any messages recently.</p>
      </div>
    <?php else: ?>
      <?php include __DIR__ . '/../../../pages/components/message-list.php'; ?>
    <?php endif; ?>
  </div>
</section>