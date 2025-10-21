<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

$teacherId = $_SESSION['user_id'];
$classStmt = $pdo->prepare("SELECT id, name, grade_level, section FROM classes WHERE adviser_id = ?");
$classStmt->execute([$teacherId]);
$advisoryClasses = $classStmt->fetchAll(PDO::FETCH_ASSOC);

renderHead('Teacher');
?>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <div id="flashContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 space-y-2 w-full max-w-sm sm:max-w-md"></div>
    <?php showFlash(); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div  class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <div class="flex justify-center items-center gap-2">
          <img src="/assets/img/class-advisory.png" class="w-5 h-5" alt="Class Advisory icon">
          <h1 class="font-bold text-md sm:text-lg">My Advisory Classes</h1>
        </div>
      </div>

    </section>
  </main>

  <?php
  include('../../includes/footer.php');
  include('../../includes/modals.php');
  ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>