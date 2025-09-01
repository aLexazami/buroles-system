<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Prevent redeclaration if included multiple times
if (!function_exists('setFlash')) {
  function setFlash($type, $message) {
    $_SESSION['flash'][] = [
      'type' => $type,
      'message' => $message
    ];
  }

  function showFlash() {
    if (empty($_SESSION['flash'])) return;

    $colors = [
      'success' => 'green',
      'error'   => 'red',
      'warning' => 'yellow'
    ];

    foreach ($_SESSION['flash'] as $flash) {
      $type = $flash['type'];
      $message = htmlspecialchars($flash['message']);
      $color = $colors[$type] ?? 'gray';

      echo "<div role='alert' aria-live='assertive' data-alert
        class='px-4 py-3 mb-4 rounded shadow-sm bg-{$color}-100 border border-{$color}-300 text-{$color}-800 flex justify-between items-center'>
        <span>{$message}</span>
        <button onclick='this.parentElement.remove()' class='ml-4 text-sm text-{$color}-700 hover:text-{$color}-900' aria-label='Dismiss'>✖</button>
      </div>";
    }

    unset($_SESSION['flash']);
  }
}
?>