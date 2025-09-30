<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Prevent redeclaration if included multiple times
if (!function_exists('setFlash')) {
  function setFlash($type, $message)
  {
    $_SESSION['flash'][] = [
      'type' => $type,
      'message' => $message
    ];
  }

  function showFlash()
  {
    if (empty($_SESSION['flash'])) return;

    $styleMap = [
      'success' => [
        'class' => 'bg-green-100 border-green-300 text-green-800',
        'button' => 'text-green-700 hover:text-green-900',
        'icon' => '/assets/img/success-icon.png'
      ],
      'error' => [
        'class' => 'bg-red-100 border-red-300 text-red-800',
        'button' => 'text-red-700 hover:text-red-900',
        'icon' => '/assets/img/error-icon.png'
      ],
      'warning' => [
        'class' => 'bg-yellow-100 border-yellow-300 text-yellow-800',
        'button' => 'text-yellow-700 hover:text-yellow-900',
        'icon' => '/assets/img/warning-icon.png'
      ]
    ];

    foreach ($_SESSION['flash'] as $flash) {
      $type = $flash['type'];
      $message = htmlspecialchars($flash['message']);
      $style = $styleMap[$type] ?? [
        'class' => 'bg-gray-100 border-gray-300 text-gray-800',
        'button' => 'text-gray-700 hover:text-gray-900',
        'icon' => '/assets/img/info-icon.png'
      ];

      echo "<div role='alert' aria-live='assertive' data-alert
    class='fixed top-4 sm:top-15 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-sm sm:max-w-md px-4 py-3 border-2 rounded shadow-lg {$style['class']} flex items-center justify-between transition-all duration-300'>
    <div class='flex items-center space-x-3'>
      <img src='{$style['icon']}' alt='{$type} icon' class='w-4 sm:w-5 h-4 sm:h-5'>
<span class='text-xs sm:text-sm font-medium text-gray-800'>{$message}</span>
    </div>
  </div>";
    }

    unset($_SESSION['flash']);
  }
}
