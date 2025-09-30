<?php
require_once __DIR__ .'/../includes/functions.php';
require_once __DIR__ .'/../controllers/respondent-counts.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/styles.css" rel="stylesheet">
  <title>Admin Dashboard</title>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Header -->
  <?php include '../includes/header.php' ?>

  <!-- Main Layout -->
<main class="grid grid-cols-1 md:grid-cols-[248px_1fr] min-h-screen">

    <!-- Desktop and Mobile Left Sidebar -->
      <?php include '../includes/side-nav-admin.php' ?>

  
    <!-- Main Content -->
    <section class="w-full p-4 space-y-6">
      <?php showFlash(); ?>
      <?php include __DIR__ . '/dashboard/dashboard-card.php'; ?>

      <!-- Respondent Summary -->
      <div class="bg-white rounded-lg shadow-sm p-4 space-y-4">
        <div class="bg-gray-300 flex justify-center items-center gap-2 py-2 rounded">
          <h1 class="font-bold text-lg">Respondents</h1>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
    </section>
  </main>

  <!-- Footer -->
  <?php include '../includes/footer.php' ?>

  <script src="../assets/js/update-dashboard.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="../assets/js/date-time.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>

  <!-- Sidebar Toggle Script -->
  <script>
    const openSidebar = document.getElementById('open-sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const mobileSidebar = document.getElementById('mobile-sidebar');

    openSidebar?.addEventListener('click', () => {
      mobileSidebar.classList.remove('-translate-x-full');
    });

    closeSidebar?.addEventListener('click', () => {
      mobileSidebar.classList.add('-translate-x-full');
    });
  </script>
</body>
</html>