<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/respondent-counts.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Admin');
?>

<body class="bg-gray-200 min-h-screen">

  <!-- Header -->
  <?php include '../includes/header.php' ?>

<!-- Main Layout -->
<main class="grid grid-cols-1 md:grid-cols-[auto_1fr_300px] lg:grid-cols-[auto_1fr_300px]">

  <?php showFlash(); ?>

  <!-- Sidebar Left (flush to edge) -->
  <div class="order-1 lg:order-none h-full">
    <?php include '../includes/side-nav-admin.php' ?>
  </div>

  <!-- Center Content (padded and centered) -->
  <section class="order-2 lg:order-none lg:col-span-1 px-4 py-4 w-full">
    <div class="space-y-6 max-w-full bg-white h-full py-6 px-4 rounded shadow">
      <div class="space-y-4">
        <div class="bg-gray-300 flex justify-center items-center gap-2 py-2 rounded">
          <h1 class="font-bold text-lg">Respondents</h1>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Cards -->
          <div class="bg-white shadow-md rounded-lg p-4 text-center">
            <img src="/assets/img/new.png" class="mx-auto h-16 w-16 mb-2" alt="New Respondents">
            <p class="text-sm text-gray-500 uppercase">New</p>
            <p id="new-count" class="text-2xl font-bold"><?= $newCount ?></p>
          </div>
          <div class="bg-white shadow-md rounded-lg p-4 text-center">
            <img src="/assets/img/weekly.png" class="mx-auto h-16 w-16 mb-2" alt="Weekly Respondents">
            <p class="text-sm text-gray-500 uppercase">Weekly</p>
            <p id="weekly-count" class="text-2xl font-bold"><?= $weeklyCount ?></p>
          </div>
          <div class="bg-white shadow-md rounded-lg p-4 text-center">
            <img src="/assets/img/total.png" class="mx-auto h-16 w-16 mb-2" alt="Total Respondents">
            <p class="text-sm text-gray-500 uppercase">Total</p>
            <p id="annual-count" class="text-2xl font-bold"><?= $annualCount ?></p>
          </div>
        </div>
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