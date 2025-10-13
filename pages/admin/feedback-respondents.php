<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/table-utils.php';
require_once __DIR__ . '/../../helpers/head.php';
require_once __DIR__ . '/../../helpers/flash.php';
renderHead('Admin');
?>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>
  <?php showFlash() ?>

  <!-- Responsive Layout -->
  <main class="flex min-h-screen w-full overflow-x-auto">
    <?php include('../../includes/side-nav-admin.php'); ?>

    <section class="w-full m-4">
      <!-- Page Title -->
      <div class="bg-emerald-300 p-2 flex justify-center items-center gap-2 mb-5">
        <img src="/assets/img/feedback-respondent.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Feedback icon">
        <h1 class="font-bold text-lg md:text-xl">Feedback Respondents</h1>
      </div>

      <!-- Table Container -->
      <div class="bg-white rounded-lg shadow-md px-4 py-6 md:px-6 md:py-10">

        <!-- Controls: Export + View Full -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
          <!-- Export Dropdown -->
          <div class="relative inline-block text-left">
            <button id="exportToggle"
              class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition cursor-pointer flex items-center gap-2">
              <img src="/assets/img/export-icon.png" alt="Export" class="w-5 h-5 invert">
              Export
            </button>
            <div id="exportMenu"
              class="hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg">
              <form action="/controllers/export-feedback-csv.php" method="POST">
                <input type="hidden" name="format" value="csv">
                <button type="submit"
                  class="flex items-center gap-5 w-full text-left px-4 py-2 hover:bg-emerald-100 cursor-pointer">
                  <img src="/assets/img/csv-icon.png" alt="CSV" class="w-5 h-5">
                  Export as CSV
                </button>
              </form>
            </div>
          </div>

          <!-- View Full Button with Tooltip -->
          <div class="relative group text-left">
            <button onclick="window.location.href='/pages/admin/feedback-details.php'"
              class="cursor-pointer hover:bg-emerald-100 rounded-md p-2 transition-transform duration-200 hover:scale-105 flex items-center gap-2 bg-gray-100">
              <img src="/assets/img/fullscreen.png" class="w-5 h-5" alt="Fullscreen icon">
              <span class="text-sm font-medium text-gray-700">View Full</span>
            </button>
            <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
              View full details
            </div>
          </div>
        </div>

        <!-- Table Output -->
        <div class="overflow-x-auto">
          <div id="respondentsTableContainer" data-sort-by="id" data-sort-order="desc" class="min-h-[450px]"></div>
        </div>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
</body>

</html>