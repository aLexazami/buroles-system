<?php
require_once __DIR__ . '/../config/database.php';

// Get user info
$userId = $_SESSION['user_id'] ?? null;
$currentPage = basename($_SERVER['PHP_SELF']);
$activeRole = $_SESSION['active_role_id'] ?? null;
$originalRoleId = $_SESSION['original_role_id'] ?? null;
$availableRoles = $_SESSION['available_roles'] ?? [];
$canSwitchRoles = in_array(2, $availableRoles) || in_array(99, $availableRoles);

// Unread message count (based on message_user)
$stmt = $pdo->prepare("
  SELECT COUNT(*) FROM message_user mu
  JOIN messages m ON mu.message_id = m.id
  WHERE mu.user_id = ? AND mu.is_read = 0 AND mu.is_deleted = 0 AND m.recipient_id = ?
");
$stmt->execute([$userId, $userId]);
$unreadMessages = $stmt->fetchColumn() ?? 0;

// Unread notification count
$notifStmt = $pdo->prepare("
  SELECT COUNT(*) FROM notifications
  WHERE (user_id = :userId OR role_id = :roleId) AND is_read = 0
");
$notifStmt->execute([
  'userId' => $userId,
  'roleId' => $originalRoleId
]);
$unreadNotifs = $notifStmt->fetchColumn() ?? 0;

// Include nav map after counts are available
require_once __DIR__ . '/role-nav-map.php';
?>

<header class="shadow-md sticky-top-0 z-10 bg-emerald-950 text-white p-1">
  <section class="max-w-7xl m-auto flex justify-between px-10 items-center">
    <!-- Logo and School Name -->
    <div class="flex items-center py-2">
      <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-12 border rounded-full bg-white">
      <p class="text-xl font-medium ml-5">Burol Elementary School</p>
    </div>

    <!-- Date and Time -->
    <div class="flex text-sm font-bold">
      <span id="date-time"></span>
    </div>

    <!-- User and Nav -->
    <div class="flex justify-end items-center">
      <!-- Desktop Nav -->
      <div class="max-md:hidden flex space-x-2">
        <div class="flex space-x-2 relative">
          <?php include 'role-badge.php'; ?>

          <!-- Profile Button -->
          <button id="menu-btn-desktop" class="flex flex-row items-center space-x-3 cursor-pointer mr-2">
            <img src="<?= htmlspecialchars($_SESSION['avatar_path'] ?? '/assets/img/user.png') . '?v=' . time() ?>" alt="Profile" class="h-10 w-10 rounded-full border-2 border-emerald-400">
            <div>
              <p class="font-medium"><?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) ?></p>
              <p class="uppercase text-sm"><?= htmlspecialchars($_SESSION['role_name']) ?></p>
            </div>
          </button>

          <!-- Role Switcher -->
          <?php if ($canSwitchRoles && count($availableRoles) >= 1): ?>
            <div id="role-switcher-desktop" class="absolute top-full right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50 hidden px-4 py-2 space-y-1">
              <?php foreach ($availableRoles as $role): ?>
                <a href="#" class="block px-3 py-2 text-sm rounded hover:bg-emerald-100 text-emerald-800 <?= $role == $activeRole ? 'font-bold bg-emerald-50' : '' ?>" data-role="<?= $role ?>">
                  <?= $role == 1 ? 'Staff' : ($role == 2 ? 'Admin' : 'Super Admin') ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Nav Items with Badge -->
        <?php foreach ($navItems as $item): ?>
          <?php $isActive = basename($item['link']) === $currentPage; ?>
          <div class="flex items-center relative">
            <a href="<?= $item['link'] ?>" class="group relative flex items-center p-2 text-sm rounded-sm <?= $isActive ? 'bg-emerald-700 text-white font-bold' : 'text-emerald-800 hover:bg-emerald-600' ?>">
              <div class="relative">
                <img src="/assets/img/<?= $item['icon'] ?>" alt="<?= $item['label'] ?>" class="h-5 w-5 invert">
                <span data-badge="<?= $item['label'] ?>"
                  class="absolute -top-3 -right-2 bg-red-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center"
                  style="<?= empty($item['count']) ? 'display:none;' : '' ?>">
                  <?= $item['count'] ?>
                </span>
              </div>
              <span class="absolute top-10 opacity-0 translate-y-1 transition-all duration-300 text-sm bg-white text-emerald-800 px-2 py-1 rounded group-hover:opacity-100 group-hover:translate-y-0">
                <?= $item['label'] ?>
              </span>
            </a>
          </div>
        <?php endforeach; ?>

        <!-- Logout -->
        <div class="flex items-center">
          <a href="/controllers/log-out.php" class="group relative flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600">
            <img src="/assets/img/logout.png" alt="Logout" class="h-5 w-5">
            <span class="absolute top-10 opacity-0 translate-y-1 transition-all duration-300 text-sm bg-white text-emerald-800 px-2 py-1 rounded group-hover:opacity-100 group-hover:translate-y-0">
              Logout
            </span>
          </a>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div class="flex flex-row">
        <button id="menu-btn-mobile" class="flex flex-row items-center space-x-3 cursor-pointer md:hidden mr-2">
          <img src="<?= htmlspecialchars($_SESSION['avatar_path'] ?? '/assets/img/user.png') . '?v=' . time() ?>" alt="Profile" class="h-10 w-10 rounded-full border-2 border-emerald-400">
          <div>
            <p class="font-medium"><?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) ?></p>
            <p class="uppercase text-sm"><?= htmlspecialchars($_SESSION['role_name']) ?></p>
          </div>
        </button>
        <div id="menu-links" class="hidden md:hidden absolute top-17 max-md:top-20 p-3 bg-white shadow-lg rounded-sm">
          <?php foreach ($navItems as $item): ?>
            <?php $isActive = basename($item['link']) === $currentPage; ?>
            <a href="<?= $item['link'] ?>" class="menu-link relative flex items-center p-2 text-sm rounded-sm <?= $isActive ? 'bg-emerald-700 text-white font-bold' : 'text-emerald-800 hover:bg-emerald-600' ?>">
              <div class="relative">
                <img src="/assets/img/<?= $item['icon'] ?>" alt="<?= $item['label'] ?>" class="h-5 w-5 rounded-full mr-3">
                <span data-badge="<?= $item['label'] ?>"
                  class="absolute -top-2 -right-1 bg-red-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center"
                  style="<?= empty($item['count']) ? 'display:none;' : '' ?>">
                  <?= $item['count'] ?>
                </span>
              </div>
              <?= $item['label'] ?>
            </a>
          <?php endforeach; ?>
          <a href="/controllers/log-out.php" class="menu-link flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600">
            <img src="/assets/img/logout.png" alt="Logout" class="h-5 w-5 rounded-full mr-3">Logout
          </a>
        </div>
      </div>
    </div>
  </section>
</header>

<div>
  <?php include __DIR__ . '/../includes/role-welcome.php' ?>
</div>