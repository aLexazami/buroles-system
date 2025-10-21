<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

renderHead('Admin');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/school-year.png" class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-base sm:text-lg md:text-xl">School Year Management</h1>
      </div>

      <!-- School Year Table + Add/Edit Modal -->
      <div class="mb-8">
        <div class="flex justify-start mb-4">
          <button data-action="add-school-year" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
            <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
            <span>Add School Year</span>
          </button>
        </div>

        <div class="w-full overflow-x-auto">
          <table class="min-w-[640px] w-full table-auto bg-white rounded shadow overflow-hidden">
            <thead class="bg-emerald-600 text-white text-left text-xs sm:text-sm">
              <tr>
                <th class="py-2 px-4 ">School Year</th>
                <th class="py-2 px-4">Start Date</th>
                <th class="py-2 px-4">End Date</th>
                <th class="py-2 px-4">Status</th>
                <th class="py-2 px-4  flex flex-wrap justify-start sm:justify-center">Actions</th>
              </tr>
            </thead>
            <tbody id="schoolYearTableBody" class="text-sm text-gray-800 text-left">
              <!-- Rows will be injected by JS -->
            </tbody>
          </table>
        </div>

        <div id="schoolYearFallback" class="bg-white rounded-b flex shadow flex-col items-center justify-center py-10 text-gray-500 text-xs sm:text-sm hidden">
          <img src="/assets/img/empty-school-year.png" alt="No school years" class="w-16 h-16 mb-4 opacity-50">
          <p>No school years found. Click “Add School Year” to create one.</p>
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