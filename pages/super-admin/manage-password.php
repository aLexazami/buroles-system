<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/role-checker.php';

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
?>
<?php //include __DIR__ . '/../../includes/debug-panel.php'
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <title>Manage Password</title>
  <link href="/src/styles.css" rel="stylesheet" />
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <!-- Header -->
  <?php include('../../includes/header.php'); ?>

  <!-- Main Layout -->
  <main class="grid grid-cols-[248px_1fr] min-h-screen">
    <!-- Sidebar -->
    <?php include('../../includes/side-nav-super-admin.php'); ?>

    <!-- Content -->
    <section class="m-4">
      <!-- Page Title -->
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/manage-user.png" class="w-5 h-5" alt="Manage icon">
        <h1 class="font-bold text-lg">Manage Password</h1>
      </div>

      <!-- Flash Messages -->
      <?php showFlash(); ?>
      <h2 class="text-lg   text-center bg-gray-200 p-2">Manage password for : <span class="text-emerald-800 font-bold"><?= htmlspecialchars($fullName) ?></span></h2>
      <!--  Form -->
      <div class="p-6 bg-white rounded-bl-lg rounded-br-lg shadow-md">
        <div class="max-w-lg  m-auto">
          <form action="/controllers/update-password.php" method="POST" class="space-y-4">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <div>
              <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
              <input type="password" name="new_password" id="new_password" required
                class="mt-1 block w-full border px-3 py-2 rounded shadow-sm focus:ring focus:border-blue-300">
            </div>
            <div>
              <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
              <input type="password" name="confirm_password" id="confirm_password" required
                class="mt-1 block w-full border px-3 py-2 rounded shadow-sm focus:ring focus:border-blue-300">
            </div>
            <div>
              <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Password Change</label>
              <textarea name="reason" id="reason" rows="8"
                class="mt-1 block w-full border px-3 py-2 rounded shadow-sm focus:ring focus:border-blue-300 resize-none"
                placeholder="Specify your reason"></textarea></div>
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
              <button type="submit" class="px-3 py-1 bg-emerald-700 text-white rounded hover:bg-emerald-500 cursor-pointer">Update</button>
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