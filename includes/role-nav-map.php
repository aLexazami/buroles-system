<?php
global $unreadMessages, $unreadNotifs;

$sharedNav = [
  'My Account' => [
    'icon' => 'profile.png',
    'link' => '/pages/header/account.php',
  ],
  'Messages' => [
    'icon' => 'message.png',
    'link' => '/pages/header/messages.php',
  ],
  'Notifications' => [
    'icon' => 'notif.png',
    'link' => '/pages/header/notifications.php',
  ],
  'Logout' => [
    'label' => 'Logout',
    'icon' => 'logout.png',
    'link' => '/controllers/log-out.php',
    'isLogout' => true,
  ],
];


$roleId = $_SESSION['role_id'];
$navItems = [];

foreach ($sharedNav as $label => $data) {
  $count = 0;
  if ($label === 'Messages') {
    $count = $unreadMessages ?? 0;
  } elseif ($label === 'Notifications') {
    $count = $unreadNotifs ?? 0;
  }

  $navItems[] = [
    'label'    => $label,
    'icon'     => $data['icon'],
    'link'     => $data['link'],
    'count'    => $count,
    'isLogout' => $data['isLogout'] ?? false
  ];
}