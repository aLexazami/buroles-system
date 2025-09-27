<?php
require_once __DIR__.'/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
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
  <title>Super Admin Dashboard</title>
</head>
<body class="bg-gray-200 min-h-screen">
  <!-- Header Section -->
  <header class=" shadow-md sticky-top-0 z-10 bg-white">
    <?php include '../includes/header.php' ?>
  </header>

  <!-- Main Content Section -->
  <!-- Main Staff Section-->
  <main class="grid grid-cols-[248px_1fr]  min-h-screen">
    <!-- Left Side Navigation -->
    <?php include '../includes/side-nav-super-admin.php' ?>

     <!-- Right Side Content -->
    <section class="m-4">
      <?php showFlash(); ?>

      <?php include __DIR__ . '/dashboard/dashboard-card.php';?>
    </section>
  </main>

  <!--Footer Section-->
 <?php include '../includes/footer.php'?>

  <script type="module" src="/assets/js/app.js"></script>
  <script src="../assets/js/date-time.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
</body>
</html>