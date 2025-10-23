<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

$gradeLevels = $pdo->query("SELECT id, level, label FROM grade_levels ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);

// 📅 Fetch all school years
$schoolYears = $pdo->query("
  SELECT id, label, is_active
  FROM school_years
  ORDER BY is_active DESC, start_year DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 🎯 Determine current active school year for header
$currentActiveSY = null;
foreach ($schoolYears as $sy) {
  if ($sy['is_active']) {
    $currentActiveSY = $sy;
    break;
  }
}

renderHead('Grade Levels');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/grade-level.png" class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-base sm:text-lg md:text-xl">Grade Levels Management</h1>
      </div>


      <!-- 🏫 School Year Header (Always shows current active year) -->
      <?php if ($currentActiveSY): ?>
        <div class="flex justify-center [font-family:'Times_New_Roman',Times,serif] items-center gap-4 mb-6 rounded p-5 bg-gradient-to-r from-emerald-800 to-teal-500 shadow text-white">
          <h1 class="font-semibold text-xl sm:text-2xl md:text-3xl leading-tight">
            <?= htmlspecialchars($currentActiveSY['label']) ?>
          </h1>
        </div>
      <?php endif; ?>

      <!-- Grade Level Table + Add/Edit Modal -->
      <div class="mb-8">
        <div class="flex justify-start mb-4">
          <button data-action="add-grade-level" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
            <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
            <span>Add Grade Level</span>
          </button>
        </div>

        <div class="w-full overflow-x-auto">
          <table class="min-w-[640px] w-full table-auto bg-white rounded shadow overflow-hidden">
            <thead class="bg-emerald-600 text-white text-left text-xs sm:text-sm">
              <tr>
                <th class="py-2 px-4">Level</th>
                <th class="py-2 px-4">Grade</th>
                <th class="py-2 px-4 flex flex-wrap justify-start sm:justify-center">Actions</th>
              </tr>
            </thead>
            <tbody id="gradeLevelTableBody" class="text-sm text-gray-800 text-left">
              <!-- Rows will be injected by JS -->
            </tbody>
          </table>
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