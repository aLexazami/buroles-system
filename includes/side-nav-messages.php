<?php
$currentView = $_GET['view'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

// Count messages
$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ?");
$stmt->execute([$userId]);
$messageCount = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND deleted_by_recipient = 0");
$stmt->execute([$userId]);
$inboxCount = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND deleted_by_recipient = 1");
$stmt->execute([$userId]);
$trashCount = $stmt->fetchColumn() ?? 0;
?>

<nav class="w-48 bg-gray-100 border-r border-gray-300 p-4 space-y-4 text-sm font-medium text-gray-700">
  <!-- Compose -->
  <a href="/pages/header/messages.php?view=compose"
    class="flex items-center space-x-2 px-3 py-2 rounded transition
      <?= ($currentView === 'compose')
        ? 'bg-emerald-100 text-emerald-700 font-semibold'
        : 'hover:bg-emerald-50 hover:text-emerald-700' ?>">
    <img src="/assets/img/composed.png" alt="Compose Icon" class="w-4 h-4" />
    <span>Compose</span>
  </a>

  <!-- Sent -->
  <a href="/pages/header/messages.php?view=sent"
    class="flex items-center justify-between px-3 py-2 rounded transition
      <?= str_contains($currentView, 'sent')
        ? 'bg-emerald-100 text-emerald-700 font-semibold'
        : 'hover:bg-emerald-50 hover:text-emerald-700' ?>">
    <div class="flex items-center space-x-2">
      <img src="/assets/img/message.png" alt="Sent Icon" class="w-4 h-4" />
      <span>Sent</span>
    </div>
    <span class="bg-emerald-600 text-white text-xs px-2 py-0.5 rounded-full"><?= $messageCount ?></span>
  </a>

  <!-- Inbox -->
  <a href="/pages/header/messages.php?view=inbox"
    class="flex items-center justify-between px-3 py-2 rounded transition
      <?= ($context === 'inbox') ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'hover:bg-emerald-50 hover:text-emerald-700' ?>">
    <div class="flex items-center space-x-2">
      <img src="/assets/img/inbox.png" alt="Inbox Icon" class="w-4 h-4" />
      <span>Inbox</span>
    </div>
    <span class="bg-emerald-600 text-white text-xs px-2 py-0.5 rounded-full"><?= $inboxCount ?></span>
  </a>

  <!-- Trash -->
  <a href="/pages/header/messages.php?view=trash"
    class="flex items-center justify-between px-3 py-2 rounded transition
      <?= str_contains($currentView, 'trash')
        ? 'bg-emerald-100 text-emerald-700 font-semibold'
        : 'hover:bg-emerald-50 hover:text-emerald-700' ?>">
    <div class="flex items-center space-x-2">
      <img src="/assets/img/trash.png" alt="Trash Icon" class="w-4 h-4" />
      <span>Trash</span>
    </div>
    <span class="bg-emerald-600 text-white text-xs px-2 py-0.5 rounded-full"><?= $trashCount ?></span>
  </a>
</nav>