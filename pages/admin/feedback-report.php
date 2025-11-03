<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/get-feedback-data-core.php';
require_once __DIR__ . '/../../helpers/head.php';

renderHead('Admin');

// Default service and date range
$stmt = $pdo->query("SELECT id FROM services ORDER BY id ASC LIMIT 1");
$defaultServiceId = $stmt->fetchColumn();
$from = date('Y') . '-01-01';
$to = date('Y') . '-12-31';


$data = getFeedbackData($pdo, $defaultServiceId, $from, $to);

?>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr] min-h-screen">
    <?php include '../../includes/side-nav-admin.php' ?>

    <section class="m-4">
      <div class="bg-emerald-300 p-2 flex justify-center items-center gap-2 mb-5 rounded-lg shadow">
        <img src="/assets/img/feedback-report.png" class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-lg">Feedback Report</h1>
      </div>

      <div class="bg-white rounded-xl shadow-md p-4 sm:p-8 space-y-2 sm:space-y-5">
        <h1 class="font-semibold text-emerald-700 text-md sm:text-xl">Generate Report</h1>

        <!-- Date Range Selector -->
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex flex-row items-center gap-2">
            <label for="fromDate" class="block text-sm font-medium text-gray-700 mb-1">From:</label>
            <input type="date" id="fromDate" class="border text-sm border-gray-300 rounded-md p-2 w-full max-w-xs">
          </div>
          <div class="flex flex-row items-center gap-2">
            <label for="toDate" class="block text-sm font-medium text-gray-700 mb-1">To:</label>
            <input type="date" id="toDate" class="border text-sm border-gray-300 rounded-md p-2 w-full max-w-xs">
          </div>
        </div>

        <!-- Button -->
        <div class="flex flex-row gap-2 ">
          <button id="toggleServiceDropdownBtn" class="bg-emerald-800 flex flex-row gap-2 text-sm sm:text-md hover:bg-emerald-700 font-semibold items-center text-white rounded-md p-2 cursor-pointer">
            <img src="/assets/img/service.png" class="w-5 h-5">
            <span>Service Availed</span>
          </button>
          <button id="exportPdfBtn" class="bg-gray-400 flex flex-row gap-2 hover:bg-gray-500 text-sm sm:text-md font-semibold items-center rounded-md p-2 cursor-pointer">
            <img src="/assets/img/export.png" class="w-5 h-5">
            <span>Export to PDF</span>
          </button>
        </div>
        <!-- Rendered Service List -->
        <div id="renderedServiceList" class="hidden mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-md text-sm sm:text-base max-h-60 overflow-y-auto transition-all duration-300 ease-in-out">
          <!-- JS will render service items here -->
        </div>
      </div>

      <div id="service-report-container" class="bg-white p-3 rounded-lg shadow mb-3 mt-4 space-y-4">
        <div class="flex flex-col px-3 py-2">
          <h2 id="reportServiceName" class="text-xl font-bold text-emerald-700">Loading...</h2>
          <span id="reportYearRange" class="text-sm text-gray-500">Loading range...</span>
        </div>

        <!-- I. Respondents -->
        <div id="respondentSection"></div>

        <!-- II. Demographics -->
        <div id="demographicsSection"></div>

        <!-- III. Citizen's Charter -->
        <div id="charterSection"></div>

        <!-- IV. Charter Summary -->
        <div id="charterSummarySection"></div>

        <!-- V. SQD Breakdown -->
        <div id="sqdSection"></div>
      </div>
    </section>
  </main>

  <?php include '../../includes/footer.php' ?>

  <script type="module" src="/assets/js/app.js"></script>
  <script type="module" src="/assets/js/service-report.js"></script>
</body>

</html>