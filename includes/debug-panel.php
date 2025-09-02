<?php
// 🧠 Show panel only if user is authenticated and has a role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_slug'])) {
  return;
}

// 🔍 Current page and role context
$currentPage = basename($_SERVER['PHP_SELF']);
$roleSlug = $_SESSION['role_slug'];

// 🧾 Available roles (IDs)
$availableRoles = $_SESSION['available_roles'] ?? [];
$availableRolesStr = !empty($availableRoles) ? implode(', ', $availableRoles) : 'None';

// 🧠 Role name logic
$originalRoleName = $_SESSION['original_role_name'] ?? $_SESSION['role_name'] ?? 'N/A';
$activeRoleName = $_SESSION['active_role_name'] ?? $originalRoleName;
?>

<div class="bg-gray-100 border border-gray-300 p-4 text-sm rounded shadow-md mb-6">
  <strong>🔍 Session Debug Panel</strong><br>
  <span><strong>CURRENT ROLE</strong></span><br>
  <span><strong>User ID:</strong> <?= $_SESSION['user_id'] ?? 'N/A' ?></span><br>
  <span><strong>Username:</strong> <?= $_SESSION['username'] ?? 'N/A' ?></span><br>
  <span><strong>Role:</strong> <?= $originalRoleName ?></span><br>
  <span><strong>Role ID:</strong> <?= $_SESSION['role_id'] ?? 'N/A' ?></span><br>
  <br>
  <span><strong>ROLE SWITCHED:</strong> <?= !empty($_SESSION['role_switched']) ? '✅ Yes' : '❌ No' ?></span><br>
  <span><strong>Active Role ID:</strong> <?= $_SESSION['active_role_id'] ?? 'N/A' ?></span><br>
  <span><strong>Currently Acting As:</strong> <?= $activeRoleName ?></span><br>
  <span><strong>Role Slug:</strong> <?= $roleSlug ?></span><br>
  <span><strong>Available Roles:</strong> <?= $availableRolesStr ?></span><br>
  <span><strong>Current Page:</strong> <?= $currentPage ?></span><br>
</div>