<?php
require_once __DIR__ .'/../includes/functions.php';
require_once __DIR__ .'/../controllers/respondent-counts.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
?>
<?php //include __DIR__ . '/../includes/debug-panel.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/styles.css" rel="stylesheet">
  <title>Admin Dashboard</title>
</head>
<body class="bg-gray-200 min-h-screen">
  <!-- Header Section -->
  <?php include '../includes/header.php' ?>

  <!-- Admin Main Content Section -->
  <main class=" grid grid-cols-[248px_1fr]  min-h-screen">
    <!-- Left Side Navigation -->
    <?php include '../includes/side-nav-admin.php' ?>

    <!-- Right Side Content -->
    <section class="m-4">
      <?php showFlash(); ?>

      <?php include __DIR__ . '/dashboard/dashboard-card.php';?>
      <br>
      <div class=" grid grid-cols-3 gap-4">
        <div class="bg-gray-300 col-span-3 flex justify-center items-center gap-2 p-2">
          <h1 class="font-bold text-lg ">Respondents</h1>
        </div>

        <!-- New -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center">
          <img src="/assets/img/new.png" class="mx-auto h-15 w-15 mb-2  ">
          <p class="text-sm text-gray-500 uppercase ">New</p>
          <p id="new-count" class="text-2xl font-bold "><?= $newCount ?></p>
        </div>

        <!-- Weekly -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center">
          <img src="/assets/img/weekly.png" class="mx-auto h-15 w-15 mb-2 ">
          <p class="text-sm text-gray-500 uppercase">Weekly</p>
          <p id="weekly-count"  class="text-2xl font-bold"><?= $weeklyCount ?></p>
        </div>

        <!-- Total -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center">
          <img src="/assets/img/total.png" class="mx-auto h-15 w-15 mb-2 ">
          <p class="text-sm text-gray-500 uppercase">Total</p>
          <p id="annual-count" class="text-2xl font-bold "><?= $annualCount ?></p>
        </div>
      </div>
    </section>
  </main>

  <!--Footer Section-->
  <?php include '../includes/footer.php' ?>

  <script src="../assets/js/update-dashboard.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="../assets/js/date-time.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
</body>
</html>
