<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

$gradeLevels = $pdo->query("SELECT id, level, label FROM grade_levels ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);

renderHead('Grade Sections');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/grade-section.png" class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-base sm:text-lg md:text-xl">Grade Sections Management</h1>
      </div>

      <!-- Section Table + Add/Edit Modal -->
      <div class="mb-8">
        <div class="flex justify-start mb-4">
          <button data-action="add-grade-section" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
            <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
            <span>Add Section</span>
          </button>
        </div>

        <div class="w-full overflow-x-auto">
          <table class="min-w-[640px] w-full table-auto bg-white rounded shadow overflow-hidden">
            <thead class="bg-emerald-600 text-white text-left text-xs sm:text-sm">
              <tr>
                <th class="py-2 px-4">Grade</th>
                <th class="py-2 px-4">Section</th>
                <th class="py-2 px-4 flex flex-wrap justify-start sm:justify-center">Actions</th>
              </tr>
            </thead>
            <tbody id="gradeSectionTableBody" class="text-sm text-gray-800 text-left">
              <!-- Rows will be injected by JS -->
            </tbody>
          </table>
        </div>

        <div id="sectionFallback" class="bg-white rounded-b flex shadow flex-col items-center justify-center py-10 text-gray-500 text-xs sm:text-sm hidden">
          <img src="/assets/img/empty-section.png" alt="No sections" class="w-16 h-16 mb-4 opacity-50">
          <p>No sections found. Click “Add Section” to create one.</p>
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