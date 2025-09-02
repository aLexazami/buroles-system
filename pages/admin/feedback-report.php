<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once  __DIR__ . '/../../auth/session.php';
require_once  __DIR__ . '/../../config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/src/styles.css" rel="stylesheet">
  <title>Feedback Report</title>
</head>

<body class="bg-gray-200  min-h-screen flex flex-col">

  <!-- Header Section -->
  <?php include('../../includes/header.php'); ?>


  <!-- Feedback Respondents Main Content Section -->
  <main class=" grid grid-cols-[248px_1fr] min-h-screen">
    <!-- Left Side Navigation Section -->
    <?php include '../../includes/side-nav-admin.php' ?>

    <!-- Right Side Context Section -->
    <section class="m-4">
      <div class="bg-emerald-300 p-2 flex justify-center items-center gap-2 mb-5">
        <img src="/assets/img/feedback-report.png " class="w-5 h-5">
        <h1 class="font-bold text-lg ">Feedback Report</h1>
      </div>
      <div class="space-y-4">
        <div class="mb-6">
          <select id="serviceSelect" class="mt-1 block w-full p-2 border rounded-lg bg-white shadow-sm">
            <option value="" disabled selected>Select a service</option>
            <?php
            // Fetch services grouped by category
            $stmt = $pdo->query("
    SELECT s.id, s.name, sc.name AS category
    FROM services s
    JOIN service_categories sc ON s.category_id = sc.id
    ORDER BY sc.name, s.name
  ");
            $grouped = [];
            while ($row = $stmt->fetch()) {
              $grouped[$row['category']][] = $row;
            }

            foreach ($grouped as $category => $services) {
              echo "<optgroup label=\"" . htmlspecialchars($category) . "\">";
              foreach ($services as $service) {
                echo "<option value=\"{$service['id']}\">" . htmlspecialchars($service['name']) . "</option>";
              }
              echo "</optgroup>";
            }
            ?>
          </select>
        </div>
      </div>
      <div id="service-report-container" class="mt-6 p-4 bg-white rounded-lg shadow space-y-4">
        <p class="text-gray-500">Select a service to view its report.</p>
      </div>

    </section>
  </main>

  <!--Footer Section-->
  <?php include '../../includes/footer.php' ?>

  <script type="module" src="/assets/js/app.js"></script>
  <script type="module" src="/assets/js/service-report.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>