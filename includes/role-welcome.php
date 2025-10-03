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
 <div class="bg-white border border-emerald-600 p-2 shadow-md flex flex-wrap items-center justify-center gap-2 sm:gap-4 text-center sm:text-left">
  <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4 w-full sm:w-auto">
    <h1 class="text-sm sm:text-base md:text-base lg:text-base font-bold text-emerald-800">
      Welcome, <?= $firstName ?>!
    </h1>
    <p class="text-sm sm:text-base md:text-base lg:text-base text-gray-600">
      You are viewing the <strong><?= $roleLabels[$activeRole] ?></strong> Dashboard.
    </p>
  </div>
</div>
<?php

endif;
?>