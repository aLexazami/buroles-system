<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$classId = $_GET['class_id'] ?? null;
$backUrl = $classId ? "/pages/admin/student-list.php?class_id=$classId" : "/pages/admin/student.php";

$navSections = [
  ['label' => 'Back', 'href' => $backUrl, 'icon' => 'back.png']
];
?>

<!-- Mobile Sidebar -->
<div id="mobile-sidebar" class="fixed inset-0 z-50 bg-white w-72 max-w-full p-4 space-y-4 transform -translate-x-full transition-transform duration-300 md:hidden overflow-y-auto max-h-screen">

  <!-- Profile Block -->
  <div class="flex flex-col items-center mt-5 mb-5">
    <img src="<?= htmlspecialchars($_SESSION['avatar_path'] ?? '/assets/img/user.png') . '?v=' . time() ?>"
      alt="Profile"
      class="h-16 w-16 rounded-full border-2 border-emerald-400">
    <h1 class="text-md font-medium pt-3 text-center">
      <?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) ?>
    </h1>
  </div>

  <!-- Navigation -->
  <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wide mt-4 mb-2">Navigation</h2>
  <nav class="space-y-2">
    <?php foreach ($navSections as $item): ?>
      <?php $isActive = $currentPage === basename($item['href']); ?>
      <a href="<?= $item['href'] ?>"
        class="group flex items-center px-3 py-2 rounded-md hover:text-emerald-600 <?= $isActive ? 'text-emerald-600 font-bold' : 'text-black' ?>">
        <img src="/assets/img/<?= $item['icon'] ?>" class="w-5 h-5 mr-2" alt="<?= $item['label'] ?>">
        <span class="text-sm"><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</div>

<!-- Desktop Sidebar -->
<aside class="hidden md:flex flex-col bg-white shadow h-full min-h-screen w-[64px] lg:w-[248px] shrink-0">

  <!-- Profile Block (visible only on lg and up) -->
  <div class="hidden lg:flex flex-col items-center py-6">
    <img src="<?= htmlspecialchars($_SESSION['avatar_path'] ?? '/assets/img/user.png') . '?v=' . time() ?>"
      alt="Profile"
      class="h-16 w-16 rounded-full border-2 border-emerald-400">
    <h1 class="text-md font-medium pt-3 text-center">
      <?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) ?>
    </h1>
  </div>

  <!-- Navigation -->
  <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wide mt-4 mb-2 pl-4 lg:pl-8">Navigation</h2>
  <nav class="space-y-2 pl-1 lg:pl-8 mt-1 py-4">
    <?php foreach ($navSections as $item): ?>
      <?php $isActive = $currentPage === basename($item['href']); ?>
      <a href="<?= $item['href'] ?>"
        class="group flex flex-col lg:flex-row items-center lg:justify-start justify-center gap-1 lg:gap-3 py-2 hover:text-emerald-600 <?= $isActive ? 'text-emerald-600 font-bold' : 'text-black' ?>">
        <img src="/assets/img/<?= $item['icon'] ?>" class="w-4 h-4" alt="<?= $item['label'] ?>">
        <span class="text-[10px] lg:text-sm hidden lg:inline"><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>