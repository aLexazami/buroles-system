<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/role-checker.php';
require_once __DIR__ . '/../../helpers/head.php';

if (!hasRoleSlug('admin') && !hasRoleSlug('super_admin')) {
  header('Location: /unauthorized.php');
  exit;
}

$userId = $_GET['id'] ?? null;
if (!$userId) {
  echo "Missing user ID.";
  exit;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT id, first_name, middle_name, last_name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
  echo "User not found.";
  exit;
}

$middleInitial = $user['middle_name'] ? strtoupper($user['middle_name'][0]) . '.' : '';
$fullName = trim("{$user['first_name']} {$middleInitial} {$user['last_name']}");
renderHead('Super Admin');
?>
<?php //include __DIR__ . '/../../includes/debug-panel.php'?>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <!-- Header -->
  <?php include('../../includes/header.php'); ?>

  <!-- Main Layout -->
  <main class="grid grid-cols-1 md:grid-cols-[248px_1fr] min-h-screen">
    <!-- Sidebar -->
    <?php include('../../includes/side-nav-super-admin.php'); ?>

    <!-- Content -->
    <section class="m-4">
      <!-- Page Title -->
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/manage-user.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Manage icon">
        <h1 class="font-bold text-base sm:text-lg md:text-xl">Manage Password</h1>
      </div>

      <!-- Flash Messages -->
      <?php showFlash(); ?>
      <h2 class="text-base sm:text-lg text-center bg-gray-200 p-2">Manage password for : <span class="text-emerald-800 font-bold"><?= htmlspecialchars($fullName) ?></span></h2>
      <!--  Form -->
      <div class="p-6 bg-white rounded-bl-lg rounded-br-lg shadow-md">
        <div class="max-w-lg w-full mx-auto px-4 sm:px-6">
          <form action="/controllers/update-password.php" method="POST" class="space-y-4">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <div class="relative">
              <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
              <input type="password" name="new_password" id="new_password" required
                class="mt-1 block w-full border px-3 py-2 rounded shadow-sm focus:ring focus:border-blue-300">
              <img
                src="/assets/img/eye-open.png"
                alt="Toggle visibility"
                class="absolute right-3 top-9 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
                data-toggle-password="new_password" />
            </div>
            <div class="relative">
              <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
              <input type="password" name="confirm_password" id="confirm_password" required
                class="mt-1 block w-full border px-3 py-2 rounded shadow-sm focus:ring focus:border-blue-300">
              <img
                src="/assets/img/eye-open.png"
                alt="Toggle visibility"
                class="absolute right-3 top-9 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
                data-toggle-password="confirm_password" />
            </div>
            <div>
              <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Password Change</label>
              <textarea name="reason" id="reason" rows="8"
                class="mt-1 block w-full border px-3 py-2 rounded shadow-sm focus:ring focus:border-blue-300 resize-none"
                placeholder="Specify your reason"></textarea>
            </div>
            <?php
            $shouldUnlock = isset($_GET['unlock']) && $_GET['unlock'] == '1';
            ?>
            <?php if ($shouldUnlock): ?>
              <label class="flex items-center gap-2 mt-4">
                <input type="checkbox" class="form-checkbox" checked disabled>
                <span class="text-sm text-gray-700">Unlock account after password reset</span>
              </label>
              <input type="hidden" name="unlock_user" value="1">
            <?php endif; ?>
            <div class="flex justify-end gap-2">
              <a href="/pages/super-admin/manage-users.php" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Cancel</a>
              <button type="submit" class="px-4 py-2 text-sm sm:text-base bg-emerald-700 text-white rounded hover:bg-emerald-500 cursor-pointer">Update</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>

  <!--  Footer -->
  <?php include('../../includes/footer.php'); ?>

  <!--  Scripts -->
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>