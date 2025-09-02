<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/fetch-feedback-data.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <title>Feedback Respondents</title>
  <link href="/src/styles.css" rel="stylesheet" />
  <!-- DataTables CSS & JS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-200 min-h-screen flex flex-col">
  <!-- 🔹 Header -->
  <?php include('../../includes/header.php'); ?>

  <!-- 🔹 Main Layout -->
  <main class="grid grid-cols-[248px_1fr] min-h-screen">
    <!-- 🔸 Sidebar -->
    <?php include('../../includes/side-nav-admin.php'); ?>

    <!-- 🔸 Content -->
    <section class="m-4">
      <!-- 🧾 Page Title + Fullscreen Button -->
      <div class="bg-emerald-300 grid grid-cols-3">
        <div class="flex items-center justify-center gap-2 col-span-1 col-start-2">
          <img src="/assets/img/feedback-respondent.png" class="w-5 h-5" alt="Feedback icon">
          <h1 class="font-bold text-lg">Feedback Respondents</h1>
        </div>
        <div class="text-right">
          <button onclick="window.location.href='/pages/admin/feedback-details.php'"
                  class="cursor-pointer hover:bg-emerald-600 rounded-md p-1 transition-transform duration-200 hover:scale-105"
                  title="View full details">
            <img src="/assets/img/fullscreen.png" class="w-8 h-8" alt="Fullscreen icon">
          </button>
        </div>
      </div>

      <!-- 📋 Feedback Table -->
      <div class="overflow-x-auto mt-6">
        <table id="feedbackTable" class="min-w-[900px] w-full table-auto text-sm border-separate border-spacing-y-2 bg-white">
          <thead class="bg-gray-300 text-left text-black">
            <tr class="shadow-lg">
              <th>No.</th>
              <th>Name</th>
              <th>Date</th>
              <th>Age</th>
              <th>Sex</th>
              <th>Customer Type</th>
              <th>Service Availed</th>
              <th>Region</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $row): ?>
              <tr class="shadow-lg border-t hover:bg-emerald-50">
                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['date'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['age'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sex'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['customer_type'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['service_availed'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['region'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- 🔹 Footer -->
  <?php include('../../includes/footer.php'); ?>

  <!-- 🔹 Scripts -->
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
  <script>
    $(document).ready(function () {
      $('#feedbackTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        order: [[0, 'desc']],
        deferRender: true
      });
    });
  </script>
</body>
</html>