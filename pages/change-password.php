<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/user-utils.php';

if (!isset($_SESSION['user_id'])) {
  redirectTo('/index.php');
}

$userId = $_SESSION['user_id'];

if (!mustChangePassword($pdo, $userId)) {
  redirectTo('/index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new = trim($_POST['new_password'] ?? '');
  $confirm = trim($_POST['confirm_password'] ?? '');

  if ($new !== $confirm) {
    $error = "Passwords do not match.";
  } elseif (strlen($new) < 8) {
    $error = "Password must be at least 8 characters.";
  } elseif (updatePassword($pdo, $userId, $new)) {
    $roleId = getUserRole($pdo, $userId);
    $redirectTarget = match ($roleId) {
      1 => '/pages/main-staff.php',
      2 => '/pages/main-admin.php',
      default => '/index.php',
    };
    redirectTo("/pages/redirect-success.php?redirect=" . urlencode($redirectTarget));
  } else {
    $error = "Failed to update password. Please try again.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Change Password</title>
  <link href="/src/styles.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen">
  <!-- Header Section -->
  <header class="bg-emerald-950 shadow-md sticky top-0 z-10 p-1">
    <section class="max-w-4xl mx-auto flex justify-between items-center">
      <div class="flex items-center ">
        <img src="../assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-20 border rounded-full bg-white">
        <p class="text-3xl font-medium text-white ml-5">
          Burol Elementary School
        </p>
      </div>
      <nav>
        <ul class="flex space-x-4 mr-3">
          <li><a href="/pages/faqs.php" class="text-white text-md hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <main class="max-w-4xl mx-auto px-4 pt-10 ">
    <div class="flex items-center justify-center px-4">
      <div class="bg-white shadow-lg rounded-xl border border-emerald-500 w-full max-w-md p-6 space-y-6">
        <h2 class="text-2xl font-semibold text-emerald-700 text-center">Change Password</h2>
        <?php if ($error): ?>
          <div class="bg-red-100 text-red-700 px-4 py-2 rounded text-sm">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
          <div class="relative">
            <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input
              type="password"
              name="new_password"
              id="new_password"
              required
              class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
              placeholder="Enter new password" />
            <ul class="text-xs text-gray-500 space-y-1 mt-2 ml-1">
              <li>• Minimum 8 characters</li>
              <li>• At least one uppercase letter</li>
              <li>• At least one number</li>
              <li>• At least one special character (e.g. !@#$%)</li>
            </ul>
            <img
              src="/assets/img/eye-open.png"
              alt="Toggle visibility"
              class="absolute right-3 top-9 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
              data-toggle-password="new_password" />
            <div id="strengthBar" class="mt-2 h-2 bg-gray-200 rounded overflow-hidden">
              <div class="h-full transition-all duration-300 w-0"></div>
            </div>
          </div>


          <div class="relative">
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input
              type="password"
              name="confirm_password"
              id="confirm_password"
              required
              class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
              placeholder="Confirm new password" />
            <img
              src="/assets/img/eye-open.png"
              alt="Toggle visibility"
              class="absolute right-3 top-9 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
              data-toggle-password="confirm_password" />
          </div>

          <button
            type="submit"
            class="w-full bg-emerald-700 text-white py-2 px-4 rounded-lg hover:bg-emerald-600 transition duration-150">
            Update Password
          </button>
        </form>
      </div>
    </div>
  </main>

  <!--Footer Section-->
  <footer class="bg-emerald-950 absolute bottom-0 w-full">
    <section class="text-center py-3">
      <p class="text-white text-sm">
        Copyrights &copy; 2025. Burol Elementary School. All rights reserved.
      </p>
    </section>
  </footer>

  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>