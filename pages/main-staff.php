<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Staff');
?>
<?php //include __DIR__ . '/../includes/debug-panel.php' ?>
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
      <h1>Header</h>
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