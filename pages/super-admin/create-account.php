<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../config/database.php';

$formMode = 'create'; // Used by user-form.php to determine mode
?>
<?php //include __DIR__ . '/../../includes/debug-panel.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <link href="/src/styles.css" rel="stylesheet" />
  <title>Create Account</title>
</head>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <!-- Header -->
  <?php include('../../includes/header.php'); ?>

  <!--  Main Layout -->
  <main class="grid grid-cols-[248px_1fr] min-h-screen">
    <!--  Sidebar -->
    <?php include('../../includes/side-nav-super-admin.php'); ?>

    <!-- 🔸 Content -->
    <section class="m-4">
      <!-- Page Title -->
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/create-account.png" class="w-5 h-5" alt="Create icon">
        <h1 class="font-bold text-lg">Create Account</h1>
      </div>

      <!-- Flash Messages -->
      <?php showFlash(); ?>

      <!-- Form Component -->
      <div class="p-6 bg-white rounded-lg shadow-md">
        <?php include(__DIR__ . '/../../components/user-form.php'); ?>
      </div>
    </section>
  </main>

  <!-- 🔹 Footer -->
  <?php include('../../includes/footer.php'); ?>

  <!-- 🔹 Scripts -->
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>