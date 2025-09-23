<?php
$preview = mb_substr($msg['content'], 0, 100);
$isLong = mb_strlen($msg['content']) > 100;
$label = $context === 'sent' ? 'To:' : 'From:';
$name = $context === 'sent' ? $msg['recipient_name'] : $msg['sender_name'];

// Determine background class based on context and read status
$bgClass = ($context === 'trash' || $msg['is_read'])
  ? 'bg-white hover:bg-gray-100'
  : 'bg-gray-200 hover:bg-gray-300';
?>

<div class="relative p-3 pb-12 rounded shadow <?= $bgClass ?> transition cursor-pointer">
  <!-- Header -->
  <div class="text-sm text-gray-700 mb-1">
    <strong><?= $label ?> <?= htmlspecialchars($name) ?></strong>
    <span class="float-right"><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></span>
  </div>

  <!-- Subject -->
  <div class="flex gap-x-1 text-sm font-semibold text-emerald-700 mb-1">
    <p>Subject:</p>
    <p>
      <?= isset($msg['subject']) && trim($msg['subject']) !== '' ? htmlspecialchars($msg['subject']) : 'None' ?>
    </p>
  </div>

  <!-- Preview -->
  <p class="text-gray-800 text-sm mb-3">
    <?= htmlspecialchars($isLong ? $preview . '…' : $msg['content']) ?>
  </p>

  <!-- Actionbar Navigation -->
  <div class="absolute bottom-0 left-0 right-0 w-fit ml-auto flex justify-end items-center gap-x-2 px-3 py-2">
    <?php if ($context === 'trash'): ?>
      <!-- Restore Icon with Tooltip -->
      <div class="relative group">
        <form method="POST" action="/actions/message/restore-message.php">
          <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
          <button type="submit" class="rounded-full p-2 hover:bg-emerald-100 hover:scale-110 transition-transform duration-200 cursor-pointer">
            <img src="/assets/img/restore-icon.png" alt="Restore" class="w-4 h-4" />
          </button>
        </form>
        <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
          Restore
        </div>
      </div>
    <?php elseif ($context !== 'sent'): ?>
      <!-- Reply Icon with Tooltip -->
      <div class="relative group">
        <a href="/actions/message/mark-as-read-and-reply.php?message_id=<?= $msg['id'] ?>" class="block rounded-full p-2 hover:bg-emerald-100 hover:scale-110 transition-transform duration-200">
          <img src="/assets/img/reply-icon.png" alt="Reply" class="w-4 h-4" />
        </a>
        <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
          Reply
        </div>
      </div>

      <!-- Delete Icon with Tooltip -->
      <div class="relative group">
        <form method="POST" action="/actions/message/delete-message.php">
          <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
          <input type="hidden" name="context" value="<?= $context ?>">
          <button type="submit" class="rounded-full p-2 hover:bg-emerald-100 duration-200 cursor-pointer hover:scale-110 transition-transform">
            <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
          </button>
        </form>
        <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
          Delete
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>