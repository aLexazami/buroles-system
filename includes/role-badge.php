<?php
$roleLabel = match ($_SESSION['active_role_id']) {
  1 => 'Staff',
  2 => 'Admin',
  99 => 'Super Admin',
  default => 'Unknown Role'
};

// ✅ Show badge only if user is acting in a role different from their default
if ($_SESSION['active_role_id'] !== $_SESSION['default_role_id']):
?>
  <div class="flex items-center space-x-2 text-sm font-medium text-white bg-emerald-700 px-3 py-1 rounded-full shadow-sm">
    <span class="inline-block w-2 h-2 rounded-full bg-emerald-300"></span>
    <span>Currently acting as <strong><?= $roleLabel ?></strong></span>
  </div>
<?php endif; ?>