<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

$originalRole = $_SESSION['role_id'];
$activeRole = $_SESSION['active_role_id'];

// Define allowed views
$allowedViews = [
  'main-admin','main-staff','main-super-admin',
  'admin-messages-view', 'staff-messages-view', 'super-admin-messages-view',
  'sent-admin', 'sent-staff', 'sent-super-admin',
  'inbox-admin', 'inbox-staff', 'inbox-super-admin',
  'trash-admin', 'trash-staff', 'trash-super-admin'
];

// Determine requested view
$rolePrefix = match ($originalRole) {
  1 => 'staff',
  2 => 'admin',
  99 => 'super-admin',
  default => 'guest'
};

$view = $_GET['view'] ?? "{$rolePrefix}-messages-view";

// Resolve path
$viewPath = __DIR__ . "/../partials/message/{$view}.php";
$validView = in_array($view, $allowedViews) && file_exists($viewPath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="/src/styles.css" rel="stylesheet" />
  <title>Messages</title>
</head>
<body class="bg-gray-200 min-h-screen">
  <header class="shadow-md sticky-top-0 z-10 bg-white">
    <?php include __DIR__ . '/../../includes/header.php'; ?>
  </header>
  <main class="grid grid-cols-[248px_1fr] min-h-screen">
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

    <section class="m-4 ">
      <?php showFlash(); ?>
      <?php
      if ($validView) {
        include $viewPath;
      } else {
        echo "<p class='text-red-600 font-semibold'>Invalid view: <code>{$view}</code></p>";
      }
      ?>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>