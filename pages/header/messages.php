<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/message-router.php';
require_once __DIR__ . '/../../helpers/head.php';
renderHead('Messages');
?>
<body class="bg-gray-200 min-h-screen">
  <?php include __DIR__ . '/../../includes/header.php'; ?>
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
      switch ($context) {
        case 'compose':
          include __DIR__ . '/../partials/message/compose-message.php';
          break;
        case 'sent':
          include __DIR__ . '/../partials/message/sent.php';
          break;
        case 'inbox':
          include __DIR__ . '/../partials/message/inbox.php';
          break;
        case 'trash':
          include __DIR__ . '/../partials/message/trash.php';
          break;
        default:
          echo "<p class='text-red-600 font-semibold'>Unknown view context: <code>{$context}</code></p>";
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