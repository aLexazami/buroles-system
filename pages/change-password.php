<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/user-utils.php';
require_once __DIR__ . '/../helpers/head.php';

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

renderHead('Change Password');
?>

<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">
  <!-- Header Section -->
  <header class="bg-emerald-950 shadow-md sticky top-0 z-10 p-2">
    <section class="max-w-6xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-4">
        <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-14 w-14 border rounded-full bg-white">
        <p class="text-xl md:text-3xl font-medium text-white">BESIMS</p>
      </div>
      <nav>
        <ul class="flex gap-4 text-sm md:text-base mr-3">
          <li><a href="/pages/faqs.php" class="text-white hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <main class="flex-grow w-full px-4 pt-10">
    <section class="flex justify-center py-10">
      <div class="w-full justify-center flex max-w-2xl xl:max-w-3xl">
        <form method="POST" class="bg-white shadow-md rounded-lg p-6 w-full opacity-90 border-2 border-emerald-800 space-y-6">
          <h2 class="text-emerald-800 text-2xl text-center font-bold">Change Password</h2>

          <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm text-center">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <!-- New Password -->
          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
              <img src="/assets/img/password.png" class="h-5 w-5">
              <input type="password" name="new_password" id="new_password"
                class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg"
                placeholder="Enter new password" required>
              <img src="/assets/img/eye-open.png" alt="Toggle visibility"
                class="w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
                data-toggle-password="new_password">
            </div>
            <ul class="text-xs text-gray-500 space-y-1 mt-2 ml-1">
              <li>• Minimum 8 characters</li>
              <li>• At least one uppercase letter</li>
              <li>• At least one number</li>
              <li>• At least one special character (e.g. !@#$%)</li>
            </ul>
            <div id="strengthBar" class="mt-2 h-2 bg-gray-200 rounded overflow-hidden">
              <div class="h-full transition-all duration-300 w-0"></div>
            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
              <img src="/assets/img/password.png" class="h-5 w-5">
              <input type="password" name="confirm_password" id="confirm_password"
                class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg"
                placeholder="Confirm new password" required>
              <img src="/assets/img/eye-open.png" alt="Toggle visibility"
                class="w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
                data-toggle-password="confirm_password">
            </div>
          </div>

          <!-- Submit Button -->
          <div class="text-center">
            <button type="submit"
              class="w-full bg-emerald-700 text-white py-2 px-4 rounded hover:bg-emerald-600 transition duration-150 sm:text-base md:text-lg cursor-pointer">
              Update Password
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <!-- Footer Section -->
  <footer class="bg-emerald-950 w-full mt-auto">
    <section class="text-center py-3 px-4">
      <p class="text-white text-xs md:text-sm">
        &copy; 2025 Burol Elementary School. All rights reserved.
      </p>
    </section>
  </footer>

  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>