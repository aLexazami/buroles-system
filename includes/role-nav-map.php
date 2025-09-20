<?php
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
];

$roleId = $_SESSION['role_id'];

$navItems = [];

foreach ($sharedNav as $label => $data) {
  $navItems[] = [
    'label' => $label,
    'icon'  => $data['icon'],
    'link'  => $data['link'],
  ];
}
?>