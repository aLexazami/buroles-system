<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/table-utils.php';
require_once __DIR__ . '/../../includes/fetch-feedback-data.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <title>Feedback Respondents Details</title>
  <link href="/src/styles.css" rel="stylesheet" />
</head>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class=" min-h-screen">
    <section class="m-4">
      <!-- Page Title -->
      <div class="bg-emerald-300 p-2 flex justify-center items-center gap-2 mb-5">
        <img src="/assets/img/feedback-respondent.png" class="w-5 h-5" alt="Feedback icon">
        <h1 class="font-bold text-lg">Feedback Respondents Details</h1>
      </div>

      <!-- Feedback Table -->
      <div class="min-h-screen px-6 py-5 bg-white rounded-lg shadow-md">
        <!-- Export Dropdown -->
        <div class="relative inline-block text-left mb-2">
          <button id="exportToggle"
            class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 transition cursor-pointer flex items-center gap-2">
            <img src="/assets/img/export-icon.png" alt="Export" class="w-5 h-5 invert">
            Export
          </button>
          <div id="exportMenu"
            class="hidden absolute z-10 mt-2 w-45 bg-white border border-gray-200 rounded shadow-lg">
            <form action="/controllers/export-feedback-csv.php" method="POST">
              <input type="hidden" name="format" value="csv">
              <button type="submit"
                class="flex items-center gap-5 w-full text-left px-4 py-2 hover:bg-emerald-100 cursor-pointer">
                <img src="/assets/img/csv-icon.png" alt="CSV" class="w-5 h-5">
                Export as CSV
              </button>
            </form>
            <!-- Temporary Disable the PDF Export Button
            <form action="/controllers/export-feedback-pdf.php" method="POST">
              <input type="hidden" name="format" value="pdf">
              <button type="submit"
                class="flex items-center gap-5 w-full text-left px-4 py-2 hover:bg-emerald-100 cursor-pointer">
                <img src="/assets/img/pdf-icon.png" alt="PDF" class="w-5 h-5">
                Export as PDF
              </button>
            </form>
            -->
          </div>
        </div>

        <div class="flex items-center gap-2 mb-4">
          <!-- Search Bar -->
          <input
            type="text"
            id="userSearch"
            placeholder=" Search"
            class="px-4 py-2 border rounded w-full max-w-md" />
          <button
            id="clearSearch"
            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm">
            Clear
          </button>

          <!-- Back Button with Tooltip -->
          <div class="relative group text-left">
            <button onclick="window.location.href='/pages/admin/feedback-respondents.php'"
              class="cursor-pointer hover:bg-emerald-100 rounded-md p-1 transition-transform duration-200 hover:scale-105">
              <img src="/assets/img/minimize.png" class="w-6 h-6" alt="Fullscreen icon">
            </button>
            <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
              Return to feedback respondents
            </div>
          </div>
        </div>

        <div id="feedbackTableContainer" class="min-w-full text-sm text-left"></div>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>