<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

$formMode = 'create'; // Used by user-form.php to determine mode
renderHead('Super Admin');
?>
<?php //include __DIR__ . '/../../includes/debug-panel.php' ?>
<body class="bg-gray-200 min-h-screen flex flex-col">
  <!-- Header -->
  <?php include('../../includes/header.php'); ?>

  <!--  Main Layout -->
  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr] min-h-screen">
    <!--  Sidebar -->
    <?php include('../../includes/side-nav-super-admin.php'); ?>

    <!-- 🔸 Content -->
    <section class="m-4">
      <!-- Page Title -->
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/create-account.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Create icon">
        <h1 class="font-bold text-lg">Create Account</h1>
      </div>

      <!-- Flash Messages -->
      <?php showFlash(); ?>

      <!-- Form Component -->
      <div class="p-4 sm:p-6 bg-white rounded-lg shadow-md w-full max-w-4xl mx-auto">
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