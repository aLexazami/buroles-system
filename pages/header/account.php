<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
$originalRole = $_SESSION['role_id'];
$activeRole = $_SESSION['active_role_id'];
?>
<?php //include __DIR__ . '/../includes/debug-panel.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/src/styles.css" rel="stylesheet" />
  <title>My Account</title>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Header Section -->
  <header class=" shadow-md sticky-top-0 z-10 bg-white">
    <?php include __DIR__ . '/../../includes/header.php' ?>
  </header>

  <!-- Main Content Section -->
  <!-- Main Staff Section-->
  <main class=" grid grid-cols-[248px_1fr]  min-h-screen">
    <!-- Left Side Navigation -->
    <?php
    switch ($activeRole) {
        case 1:
      include __DIR__ . '/../../includes/side-nav-staff.php';
      break;
        case 2:
      include __DIR__ . '/../../includes/side-nav-admin.php';
      break;
        case 99:
      include __DIR__ . '/../../includes/side-nav-super-admin.php';
      break;
        default:
      echo "<p>Role not recognized.</p>";
      }
    ?>
    <section class="m-4">
      <?php
      switch ($originalRole) {
        case 1:
      include __DIR__ . '/../partials/staff-account-view.php';
      break;
        case 2:
      include __DIR__ . '/../partials/admin-account-view.php';
      break;
        case 99:
      include __DIR__ . '/../partials/super-admin-account-view.php';
      break;
        default:
      echo "<p>Role not recognized.</p>";
      }
      ?>
    </section>
  </main>

  <!--Footer Section-->
  <?php include __DIR__ .'/../../includes/footer.php' ?>


  <script type="module" src="/assets/js/app.js"></script>
  <script src="/../assets/js/date-time.js"></script>
</body>
</html>