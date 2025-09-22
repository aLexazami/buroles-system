<?php
$rolePrefix = '';
switch ($_SESSION['role_id'] ?? 0) {
  case 1:
    $rolePrefix = 'staff';
    break;
  case 2:
    $rolePrefix = 'admin';
    break;
  case 99:
    $rolePrefix = 'super-admin';
    break;
}

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
  <a href="/pages/header/messages.php?view=<?= $rolePrefix ?>-messages-view"
    class="flex items-center space-x-2 hover:text-emerald-700 <?= ($currentView === "{$rolePrefix}-messages-view" || empty($currentView)) ? 'text-emerald-700 font-semibold' : '' ?>">
    <img src="/assets/img/composed.png" alt="Compose Icon" class="w-4 h-4" />
    <span>Compose</span>
  </a>

  <!-- Sent -->
  <a href="/pages/header/messages.php?view=sent-<?= $rolePrefix ?>"
    class="flex items-center justify-between hover:text-emerald-700 <?= str_contains($currentView, 'sent') ? 'text-emerald-700 font-semibold' : '' ?>">
    <div class="flex items-center space-x-2">
      <img src="/assets/img/message.png" alt="Sent Icon" class="w-4 h-4" />
      <span>Sent</span>
    </div>
    <span class="bg-emerald-600 text-white text-xs px-2 py-0.5 rounded-full"><?= $messageCount ?></span>
  </a>

  <!-- Inbox -->
  <a href="/pages/header/messages.php?view=inbox-<?= $rolePrefix ?>"
    class="flex items-center justify-between hover:text-emerald-700 <?= str_contains($currentView, 'inbox') ? 'text-emerald-700 font-semibold' : '' ?>">
    <div class="flex items-center space-x-2">
      <img src="/assets/img/inbox.png" alt="Inbox Icon" class="w-4 h-4" />
      <span>Inbox</span>
    </div>
    <span class="bg-emerald-600 text-white text-xs px-2 py-0.5 rounded-full"><?= $inboxCount ?></span>
  </a>

  <!-- Trash -->
  <a href="/pages/header/messages.php?view=trash-<?= $rolePrefix ?>"
    class="flex items-center justify-between hover:text-emerald-700 <?= str_contains($currentView, 'trash') ? 'text-emerald-700 font-semibold' : '' ?>">
    <div class="flex items-center space-x-2">
      <img src="/assets/img/trash.png" alt="Trash Icon" class="w-4 h-4" />
      <span>Trash</span>
    </div>
    <span class="bg-emerald-600 text-white text-xs px-2 py-0.5 rounded-full"><?= $trashCount ?></span>
  </a>
</nav>