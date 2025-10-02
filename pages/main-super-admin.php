<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Super Admin');
?>
<?php //include __DIR__ . '/../includes/debug-panel.php'
?>

<body class="bg-gray-200 min-h-screen">

  <!-- Header -->
  <?php include '../includes/header.php' ?>

  <!-- Main Layout -->
  <main class="grid grid-cols-1 md:grid-cols-[64px_1fr_300px] lg:grid-cols-[248px_1fr_300px]">

    <?php showFlash(); ?>

    <!-- Sidebar Left (flush to edge) -->
    <div class="order-1 lg:order-none h-full">
      <?php include '../includes/side-nav-admin.php' ?>
    </div>

    <!-- Center Content (padded and centered) -->
    <section class="order-2 lg:order-none lg:col-span-1 px-4 py-4 w-full">
      <div class="space-y-4 max-w-full">
        <div class="space-y-4 max-w-full text-center">
          <h1 class="text-2xl sm:text-3xl font-bold text-emerald-700">Welcome, Super Admin!</h1>
          <p class="text-gray-700 text-sm sm:text-base leading-relaxed">
            You have full administrative access to the system. As a Super Admin, you oversee platform-wide settings, manage user roles, and ensure the integrity of announcements and data across all departments.
          </p>
          <p class="text-gray-500 italic text-xs sm:text-sm">
            Use the sidebar to navigate between administrative tools, or check the latest announcements on the right.
          </p>
        </div>
      </div>
    </section>

    <!-- Announcement Card Right (flush to edge) -->
    <aside class="order-3 lg:order-none h-full">
      <?php include __DIR__ . '/dashboard/announcement-card.php'; ?>
    </aside>

  </main>

  <!-- Footer -->
  <?php include '../includes/footer.php' ?>

  <!-- Scripts -->
  <script src="../assets/js/update-dashboard.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="../assets/js/date-time.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
</body>

</html>