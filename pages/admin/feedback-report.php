<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';
renderHead('Admin');
?>

<body class="bg-gray-200 min-h-screen flex flex-col">

  <!-- Header Section -->
  <?php include('../../includes/header.php'); ?>

  <!-- Feedback Respondents Main Content Section -->
  <main class="grid grid-cols-1 md:grid-cols-[248px_1fr] min-h-screen">
    <!-- Left Side Navigation Section -->
    <?php include '../../includes/side-nav-admin.php' ?>

    <!-- Right Side Context Section -->
    <section class="p-4">
      <div class="bg-emerald-300 p-2 flex justify-center items-center gap-2 mb-5">
        <img src="/assets/img/feedback-report.png" class="w-5 h-5">
        <h1 class="font-bold text-lg">Feedback Report</h1>
      </div>

      <div class="space-y-4">
        <div class="mb-6">
          <select
            id="serviceSelect"
            placeholder="Select a service..."
            class="w-full p-2 rounded-lg border border-gray-300 bg-white shadow-sm text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
          </select>
        </div>
      </div>

      <div id="service-report-container" class="mt-6 p-4 bg-white rounded-lg shadow space-y-4">
        <p class="text-gray-500">Select a service to view its report.</p>
      </div>
    </section>
  </main>

  <!-- Footer Section -->
  <?php include '../../includes/footer.php' ?>

  <script type="module" src="/assets/js/app.js"></script>
  <script type="module" src="/assets/js/service-report.js"></script>
  <script src="/assets/js/date-time.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
  <script>

  </script>
</body>
</html>