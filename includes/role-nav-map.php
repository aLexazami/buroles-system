<?php
$roleMenus = [
  1 => [ // Staff
    ['label' => 'My Account', 'icon' => 'profile.png', 'link' => '/pages/staff-account.php'],
    ['label' => 'Message', 'icon' => 'message.png', 'link' => '/pages/staff-messages.php'],
    ['label' => 'Notification', 'icon' => 'notif.png', 'link' => '/pages/staff-notifications.php'],
  ],
  2 => [ // Admin
    ['label' => 'Manage Users', 'icon' => 'profile.png', 'link' => '/pages/admin-users.php'],
    ['label' => 'Reports', 'icon' => 'message.png', 'link' => '/pages/admin-reports.php'],
    ['label' => 'System Logs', 'icon' => 'notif.png', 'link' => '/pages/admin-logs.php'],
  ],
  99 => [ // Super Admin
    ['label' => 'System Settings', 'icon' => 'profile.png', 'link' => '/pages/super-settings.php'],
    ['label' => 'Audit Trail', 'icon' => 'message.png', 'link' => '/pages/super-audit.php'],
    ['label' => 'Role Management', 'icon' => 'notif.png', 'link' => '/pages/super-roles.php'],
  ]
];

$baseRole = $_SESSION['role_id'] ?? 1;
$navItems = $roleMenus[$baseRole] ?? [];
?>