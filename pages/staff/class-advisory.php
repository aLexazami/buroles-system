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
      <div class="bg-emerald-300 flex justify-between items-center gap-2 p-2 mb-5">
        <div class="flex items-center gap-2">
          <img src="/assets/img/class-advisory.png" class="w-5 h-5" alt="Class Advisory icon">
          <h1 class="font-bold text-md sm:text-lg">My Advisory Classes</h1>
        </div>
      </div>

      <div class="flex justify-start mb-4">
        <button data-action="create-advisory" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
          <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
          <span>Create Advisory Class</span>
        </button>
      </div>

      <?php if ($advisoryClasses): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
          <?php foreach ($advisoryClasses as $class): ?>
            <a href="/pages/staff/student-list.php?class_id=<?= $class['id'] ?>"
               class="block bg-white border border-emerald-400 px-4 py-3 rounded shadow hover:bg-emerald-50 text-left text-sm sm:text-base">
              <div class="font-semibold"><?= htmlspecialchars($class['name']) ?></div>
              <div class="text-xs text-gray-600">Grade <?= $class['grade_level'] ?> - Section <?= htmlspecialchars($class['section']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-sm text-gray-500 mb-6">You have no advisory classes yet.</p>
      <?php endif; ?>
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