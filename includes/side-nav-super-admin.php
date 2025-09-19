<aside class="bg-gray-300 p-2 space-y-2">
  <?php
  $currentPage = basename($_SERVER['PHP_SELF']);
  ?>
  <div class="flex flex-col items-center mt-10 mb-20">
    <img src="<?= htmlspecialchars($_SESSION['avatar_path'] ?? '/assets/img/user.png') . '?v=' . time() ?>"
     alt="Profile"
     class="h-15 w-15 rounded-full border-2 border-emerald-400">
    <h1 class="text-md font-medium pt-3">
      <?php echo htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']); ?>
    </h1>
  </div>
  <?php
  $navItems = [
    ['label' => 'Dashboard', 'href' => '/pages/main-super-admin.php', 'icon' => 'home.png'],
    ['label' => 'Create Account', 'href' => '/pages/super-admin/create-account.php', 'icon' => 'create-account.png'],
    ['label' => 'Manage Users', 'href' => '/pages/super-admin/manage-users.php', 'icon' => 'manage-user.png'],
    ['label' => 'Archived Users', 'href' => '/pages/super-admin/archived-users.php', 'icon' => 'archive-user.png'],
  ];
  ?>
  <?php foreach ($navItems as $item): ?>
    <?php $isActive = $currentPage === basename($item['href']); ?>
    <a href="<?= $item['href'] ?>" class="group relative flex items-center hover:text-emerald-400 mx-2 pt-2 pr-1 pb-1 rounded-md w-fit">
      <img src="/assets/img/<?= $item['icon'] ?>" class="w-4 h-4">
<span class="ml-2 font-medium text-sm <?= $isActive ? 'text-emerald-400' : 'text-black' ?> group-hover:text-emerald-400">
  <?= $item['label'] ?>
</span>
    </a>
  <?php endforeach; ?>
</aside>
