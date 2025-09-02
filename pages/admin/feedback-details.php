<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/fetch-feedback-data.php';

// Restrict access to role ID 2 (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['active_role_id'] !== 2) {
  header("Location: ../index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex" />
  <title>Feedback Respondents Details</title>
  <link href="/src/styles.css" rel="stylesheet" />
  <!-- DataTables CSS & JS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
</head>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <!-- 🔹 Header -->
  <?php include('../../includes/header.php'); ?>

  <!-- 🔹 Main Content -->
  <main class=" min-h-screen">
    <section class="mb-10">
      <!-- 🧾 Page Title + Back Button -->
      <div class="bg-emerald-300 grid grid-cols-3">
        <div class="flex items-center justify-center gap-2 col-span-1 col-start-2">
          <img src="/assets/img/feedback-respondent.png" class="w-5 h-5" alt="Feedback icon">
          <h1 class="font-bold text-lg">Feedback Respondents</h1>
        </div>
        <div class="text-right">
          <button onclick="window.location.href='/pages/admin/feedback-respondents.php'"
                  class="cursor-pointer hover:bg-emerald-600 rounded-md p-1 transition-transform duration-200 hover:scale-105"
                  title="Back to Respondents">
            <img src="/assets/img/minimize.png" class="w-8 h-8" alt="Minimize icon">
          </button>
        </div>
      </div>

      <!-- 📋 Feedback Table -->
      <div class="overflow-x-auto mt-6">
        <table id="feedbackTable" class="bg-white min-w-[1200px] w-full table-auto text-sm border-separate border-spacing-y-2">
          <thead class="bg-gray-300 text-left text-black">
            <tr class="shadow-lg sticky top-0 z-10">
              <th>No.</th>
              <th>Name</th>
              <th>Citizen Charter Awareness</th>
              <th>CC1</th>
              <th>CC2</th>
              <th>CC3</th>
              <th>SQD1</th>
              <th>SQD2</th>
              <th>SQD3</th>
              <th>SQD4</th>
              <th>SQD5</th>
              <th>SQD6</th>
              <th>SQD7</th>
              <th>SQD8</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $row): ?>
              <tr class="shadow-lg border-t hover:bg-emerald-50">
                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['citizen_charter_awareness'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['cc1'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['cc2'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['cc3'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd1'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd2'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd3'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd4'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd5'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd6'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd7'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sqd8'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- 🔹 Footer -->
  <?php include('../../includes/footer.php'); ?>

  <!-- 🔹 DataTables Init -->
  <script>
    $(document).ready(function () {
      $('#feedbackTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        deferRender: true,
        order: [[0, 'desc']],
        fixedHeader: true,
        dom: '<"top"f>rt<"bottom"ip><"clear">'
      });
    });
  </script>
</body>
</html>