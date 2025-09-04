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

    $classMap = [
      'success' => 'bg-green-100 border-green-300 text-green-800',
      'error'   => 'bg-red-100 border-red-300 text-red-800',
      'warning' => 'bg-yellow-100 border-yellow-300 text-yellow-800',
    ];

    $buttonMap = [
      'success' => 'text-green-700 hover:text-green-900',
      'error'   => 'text-red-700 hover:text-red-900',
      'warning' => 'text-yellow-700 hover:text-yellow-900',
    ];

    foreach ($_SESSION['flash'] as $flash) {
      $type = $flash['type'];
      $message = htmlspecialchars($flash['message']);
      $class = $classMap[$type] ?? 'bg-gray-100 border-gray-300 text-gray-800';
      $buttonClass = $buttonMap[$type] ?? 'text-gray-700 hover:text-gray-900';

      echo "<div role='alert' aria-live='assertive' data-alert
    class='px-4 py-3 mb-4 rounded shadow-sm $class border flex justify-between items-center'>
    <span>{$message}</span>
    <button onclick='this.parentElement.remove()' class='ml-4 text-sm $buttonClass' aria-label='Dismiss'>✖</button>
  </div>";
    }

    unset($_SESSION['flash']);
  }
}
