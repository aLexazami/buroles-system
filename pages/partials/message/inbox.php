<?php
require_once __DIR__ . '/../../../helpers/message-router.php';
?>

<div class="bg-emerald-700 text-white px-4 py-5 sm:px-6 sm:py-6">
  <h2 class="text-lg font-semibold"><?= ucfirst($context) ?> Messages</h2>
</div>
<section class="flex flex-col md:flex-row bg-white rounded-b-lg shadow">
  <?php include __DIR__ . '/../../../includes/side-nav-messages.php'; ?>

  <div class="flex-1 px-4 sm:px-6 py-6 min-h-screen">
    <?php if ($focusedMessage): ?>
      <?php include __DIR__ . '/../../../pages/components/message-viewer.php'; ?>
    <?php else: ?>
      <?php include __DIR__ . '/../../../pages/components/message-list.php'; ?>
    <?php endif; ?>
  </div>
</section>