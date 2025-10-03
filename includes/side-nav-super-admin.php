<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
  ['label' => 'Dashboard', 'href' => '/pages/main-super-admin.php', 'icon' => 'home.png'],
  ['label' => 'Create Account', 'href' => '/pages/super-admin/create-account.php', 'icon' => 'create-account.png'],
  ['label' => 'Manage Users', 'href' => '/pages/super-admin/manage-users.php', 'icon' => 'manage-user.png'],
  ['label' => 'Archived Users', 'href' => '/pages/super-admin/archived-users.php', 'icon' => 'archive-user.png'],
];
?>

<!-- Mobile Sidebar -->
<div id="mobile-sidebar" class="fixed inset-0 z-50 bg-gray-300 w-72 max-w-full p-4 space-y-4 transform -translate-x-full transition-transform duration-300 md:hidden">
  <div class="flex flex-col items-center mt-10 mb-10">
    <img src="<?= htmlspecialchars($_SESSION['avatar_path'] ?? '/assets/img/user.png') . '?v=' . time() ?>"
      alt="Profile"
      class="h-16 w-16 rounded-full border-2 border-emerald-400">
    <h1 class="text-md font-medium pt-3 text-center">
      <?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) ?>
    </h1>
  </div>

  <h2 class="text-sm font-bold mb-5">Navigation</h2>

  <nav class="space-y-8">
    <?php foreach ($navItems as $item): ?>
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
<aside class="hidden md:flex flex-col bg-gray-300  h-full min-h-screen w-[64px] lg:w-[248px]">

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
  <nav class="space-y-2 px-2 py-4">
    <?php foreach ($navItems as $item): ?>
      <?php $isActive = $currentPage === basename($item['href']); ?>
      <a href="<?= $item['href'] ?>"
        class="group flex flex-col lg:flex-row items-center lg:justify-start justify-center gap-1 lg:gap-3 py-2 hover:text-emerald-600 <?= $isActive ? 'text-emerald-600 font-bold' : 'text-black' ?>">
        <img src="/assets/img/<?= $item['icon'] ?>" class="w-6 h-6" alt="<?= $item['label'] ?>">
        <span class="text-[10px] lg:text-sm hidden lg:inline"><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

</aside>