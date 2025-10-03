<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/user-utils.php';
require_once __DIR__ . '/../helpers/head.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/password-utils.php';

if (!isset($_SESSION['user_id'])) {
  redirectTo('/index.php');
}

$userId = $_SESSION['user_id'];

if (!mustChangePassword($pdo, $userId)) {
  redirectTo('/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newPassword = trim($_POST['new_password'] ?? '');
  $confirmPassword = trim($_POST['confirm_password'] ?? '');
  $passwordErrors = getPasswordErrors($newPassword);

  if ($newPassword !== $confirmPassword) {
    setFlash('error', 'Passwords do not match.');
    header('Location: /pages/change-password.php');
    exit;
  } elseif (!empty($passwordErrors)) {
    setFlash('error', 'Password must include: ' . formatPasswordErrors($passwordErrors) . '.');
    header('Location: /pages/change-password.php');
    exit;
  } elseif (updatePassword($pdo, $userId, $newPassword)) {
    $roleId = getUserRole($pdo, $userId);
    $redirectTarget = match ($roleId) {
      1 => '/pages/main-staff.php',
      2 => '/pages/main-admin.php',
      99 => '/pages/main-super-admin.php',
      default => '/index.php',
    };
    redirectTo("/pages/redirect-success.php?redirect=" . urlencode($redirectTarget));
  } else {
    setFlash('error', 'Failed to update password. Please try again.');
    header('Location: /pages/change-password.php');
    exit;
  }
}

renderHead('Change Password');
?>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">
  <!-- Header Section -->
  <header class="bg-emerald-950 shadow-md sticky top-0 z-10 p-2">
    <section class="max-w-6xl mx-auto flex items-center justify-between">
      <!-- Logo + Title -->
      <div class="flex items-center gap-4">
        <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-14 w-14 border rounded-full bg-white">
        <p class="text-xl md:text-3xl font-medium text-white">BESIMS</p>
      </div>

      <!-- Mobile Menu Toggle -->
      <button id="menu-btn-mobile" class="md:hidden text-white focus:outline-none cursor-pointer">
        <img src="/assets/img/menu-icon.png" alt="Menu" class="h-6 w-6">
      </button>

      <!-- Navigation Links -->
      <nav id="menu-links" class="hidden md:flex flex-col md:flex-row gap-4 text-sm md:text-base bg-emerald-950 md:bg-transparent absolute md:static top-full left-0 w-full md:w-auto px-4 py-2 md:p-0">
        <ul class="flex flex-col md:flex-row gap-4">
          <li><a href="/pages/faqs.php" class="menu-link text-white hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <main class="flex-grow w-full px-4 pt-10">
    <section class="flex justify-center py-10">
      <div class="w-full justify-center flex max-w-2xl xl:max-w-3xl">
        <?php showFlash(); ?>
        <form method="POST" class="bg-white shadow-md rounded-lg p-6 w-full opacity-90 border-2 border-emerald-800 space-y-6">
          <h2 class="text-emerald-800 text-2xl text-center font-bold">Change Password</h2>

          <!-- New Password -->
          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <div class="relative">
              <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
                <img src="/assets/img/password.png" class="h-5 w-5" />
                <input
                  type="password"
                  name="new_password"
                  id="new_password"
                  data-password-rules
                  data-rules-selector="#password-rules"
                  class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg"
                  placeholder="Enter new password"
                  required />
                <img src="/assets/img/eye-open.png" alt="Toggle visibility"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 cursor-pointer opacity-70"
                  data-toggle-password="new_password" />
              </div>
            </div>
            <ul id="password-rules" class="text-xs sm:text-sm md:text-base space-y-2 mt-2 ml-1 sm:ml-2 md:ml-4">
              <li data-rule="length" class="flex flex-wrap items-center gap-2 text-gray-500">
                <img src="/assets/img/cross-icon.png" class="h-4 w-4 rule-icon" alt="status" />
                <span>Minimum 8 characters</span>
              </li>
              <li data-rule="uppercase" class="flex flex-wrap items-center gap-2 text-gray-500">
                <img src="/assets/img/cross-icon.png" class="h-4 w-4 rule-icon" alt="status" />
                <span>At least one uppercase letter</span>
              </li>
              <li data-rule="number" class="flex flex-wrap items-center gap-2 text-gray-500">
                <img src="/assets/img/cross-icon.png" class="h-4 w-4 rule-icon" alt="status" />
                <span>At least one number</span>
              </li>
              <li data-rule="special" class="flex flex-wrap items-center gap-2 text-gray-500">
                <img src="/assets/img/cross-icon.png" class="h-4 w-4 rule-icon" alt="status" />
                <span>At least one special character (e.g. !@#$%)</span>
              </li>
              <li data-rule="match" class="flex flex-wrap items-center gap-2 text-gray-500">
                <img class=" h-4 w-4 rule-icon" src="/assets/img/cross-icon.png" />
                <span>Passwords must match</span>
              </li>
            </ul>
            <div id="strengthBar" class="mt-2 h-2 bg-gray-200 rounded overflow-hidden">
              <div class="h-full transition-all duration-300 w-0"></div>
            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <div class="relative">
              <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
                <img src="/assets/img/password.png" class="h-5 w-5" />
                <input type="password" name="confirm_password" id="confirm_password"
                  class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg"
                  placeholder="Confirm new password" required />
                <img src="/assets/img/eye-open.png" alt="Toggle visibility"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 cursor-pointer opacity-70"
                  data-toggle-password="confirm_password" />
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="text-center">
            <button type="submit"
              class="w-full bg-emerald-700 text-white py-2 px-4 rounded hover:bg-emerald-600 transition duration-150 sm:text-base md:text-lg cursor-pointer">
              Update Password
            </button>
          </div>

          <!-- Back to Login -->
          <div class="text-center mt-5">
            <a href="/index.php" class="text-emerald-800 hover:underline font-bold">Back to Login</a>
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
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script src="/assets/js/password-rules-inline.js"></script>
</body>

</html>