<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../src/styles.css" rel="stylesheet">
  <title>Staff Dashboard</title>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Header Section -->
  <header class=" shadow-md sticky-top-0 z-10 bg-white">
    <?php include '../includes/header.php' ?>
  </header>

  <!-- Main Content Section -->
  <!-- Main Staff Section-->
  <main class=" grid grid-cols-[248px_1fr]  min-h-screen">
    <!-- Left Side Navigation -->
    <?php include '../includes/side-nav-admin.php' ?>

  </main>

  <!--Footer Section-->
  <?php include '../includes/footer.php' ?>


  <script type="module" src="/assets/js/app.js"></script>
  <script src="../assets/js/date-time.js"></script>
</body>
</html>