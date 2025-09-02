<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/flash.php';
if (!isset($_SESSION['user_id']) || $_SESSION['active_role_id'] !== 99) {
  header("Location: ../index.php");
  exit();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/get-users.php';

// Context for the table
$title = "User Management";
$showActions = true;
?>
<?php include __DIR__ . '/../../includes/debug-panel.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="/src/styles.css" rel="stylesheet" />
  <title><?= $title ?></title>
</head>
<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-[248px_1fr] min-h-screen">
    <?php include('../../includes/side-nav-super-admin.php'); ?>

    <section class="m-4">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/manage-user.png " class="w-5 h-5">
        <h1 class="font-bold text-lg ">User Management</h1>
      </div>

      <!-- Confirmation Modal -->
      <div id="confirm-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center animate-fade-in">
          <p id="confirm-modal-message" class="text-gray-800 text-lg mb-4 font-medium">
            Are you sure you want to proceed?
          </p>
          <div class="flex justify-center gap-4">
            <button id="confirm-modal-yes"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300 transition">
              Yes
            </button>
            <button id="confirm-modal-no"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 transition">
              No
            </button>
          </div>
        </div>
      </div>

      <!-- Flash Messages -->
      <?php showFlash(); ?>

      <div class="p-6 bg-white rounded-lg shadow-md">
        <?php include(__DIR__ . '/../../components/user-table.php'); ?>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>