<?php
$roleLabels = [
  1 => 'Staff',
  2 => 'Admin',
  99 => 'Super Admin'
];

$activeRole = $_SESSION['active_role_id'] ?? null;
$defaultRole = $_SESSION['default_role_id'] ?? null;
$firstName = htmlspecialchars($_SESSION['firstName'] ?? 'User');

if (
  isset($_SESSION['role_switched']) &&
  $_SESSION['role_switched'] === true &&
  $activeRole !== $defaultRole &&
  isset($roleLabels[$activeRole])
):
?>
  <div class="bg-white border border-emerald-600 p-2 shadow-md flex items-center space-x-4 justify-center">
  <div class="text-emerald-600 text-2xl">👋</div>
  <div class="flex gap-4">
    <h1 class="text-md font-bold text-emerald-800">Welcome, <?= $firstName ?>!</h1>
    <p class="text-md text-gray-600">You are viewing the <strong><?= $roleLabels[$activeRole] ?></strong> Dashboard.</p>
  </div>
<?php
  
endif;
?>