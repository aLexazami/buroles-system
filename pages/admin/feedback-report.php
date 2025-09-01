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

  <script src="/assets/js/button.js"></script>
  <script src="/assets/js/date-time.js"></script>

  <script>
    document.getElementById('serviceSelect').addEventListener('change', function() {
      const serviceId = this.value;
      const serviceName = this.options[this.selectedIndex].text;

      fetch(`/controllers/get-feedback-data.php?service_id=${serviceId}&year=2025`)
        .then(res => res.json())
        .then(data => {
          const container = document.getElementById('service-report-container');
          container.innerHTML = `
        <h2 class="text-xl font-bold text-emerald-700 pl-3">${serviceName}</h2>
        <div class="">
          <div class="bg-white p-3 rounded-lg shadow mb-3">
            <h1><strong>I. Total number of clients who completed the survey for FY <?= date('Y') ?>:</strong></h1>
            <span class="text-red-500 font-bold ">${data.respondents}</span>
          </div>
          <div class="bg-white p-3 rounded-lg shadow mb-3">
            <h1><strong>II. Total number of transactions for FY <?= date('Y') ?>:</strong></h1>
            <span class="text-red-500 font-bold">0</span>
          </div>
          <div class="bg-white p-3 rounded-lg shadow mb-3">
            <h1><strong>III. Demographic profile</strong></h1>
            <br>
            <div class="grid grid-cols-3 gap-4 mb-4">
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">A. Age</h1>
                <div class="pl-2 pt-2">
                  <p>● 19 or Lower</p>
                  <span id="age-19" class="text-red-500 font-bold">${data.age['19_or_lower']}</span>
                  <br>
                  <br>
                  <p>● 20 - 34</p>
                  <span id="age-20-34" class="text-red-500 font-bold">${data.age['20_34']}</span>
                  <br>
                  <br>
                  <p>● 35 - 49</p>
                  <span id="age-35-49" class="text-red-500 font-bold">${data.age['35_49']}</span>
                  <br>
                  <br>
                  <p>● 50 - 64</p>
                  <span id="age-50-64" class="text-red-500 font-bold">${data.age['50_64']}</span>
                  <br>
                  <br>
                  <p>● 65 or Higher</p>
                  <span id="age-65" class="text-red-500 font-bold">${data.age['65_or_higher']}</span>
                </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">B. Sex</h1>
                <div class="pl-2 pt-2">
                  <p>● Female</p>
                  <span class="text-red-500 font-bold">${data.female}</span>
                  <br>
                  <br>
                  <p>● Male</p>
                  <span class="text-red-500 font-bold">${data.male}</span>
                </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">C. Customer Type</h1>
                <div class="pl-2 pt-2">
                  <p>● Citizen</p>
                  <span id="type-citizen" class="text-red-500 font-bold">${data.customer_types['Citizen']}</span>
                  <br>
                  <br>
                  <p>● Business</p>
                  <span id="type-business" class="text-red-500 font-bold">${data.customer_types['Business']}</span>
                  <br>
                  <br>
                  <p>● Government</p>
                  <span id="type-government" class="text-red-500 font-bold">${data.customer_types['Government']}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-white p-3 rounded-lg shadow mb-3">
            <h1><strong>IV. Count of Citizen’s Charter Responses</strong></h1>
            <br>
              <div class="grid grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow ">
                  <h1 class="font-medium">A. Citizen's Charter Awareness (CC1)</h1>
                    <div class="pl-2 pt-2">
                      <p>● 1</p>
                      <span id="cc1-1" class="text-red-500 font-bold">${data.charter.cc1[1]}</span>
                      <br>
                      <br>
                      <p>● 2</p>
                      <span id="cc1-2" class="text-red-500 font-bold">${data.charter.cc1[2]}</span>
                      <br>
                      <br>
                      <p>● 3</p>
                      <span id="cc1-3" class="text-red-500 font-bold">${data.charter.cc1[3]}</span>
                      <br>
                      <br>
                      <p>● 4</p>
                      <span id="cc1-4" class="text-red-500 font-bold">${data.charter.cc1[4]}</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow ">
                  <h1 class="font-medium">B. Citizen’s Charter Visibility (CC2)</h1>
                    <div class="pl-2 pt-2">
                      <p>● 1</p>
                      <span id="cc2-1" class="text-red-500 font-bold">${data.charter.cc2[1]}</span>
                      <br>
                      <br>
                      <p>● 2</p>
                      <span id="cc2-2" class="text-red-500 font-bold">${data.charter.cc2[2]}</span>
                      <br>
                      <br>
                      <p>● 3</p>
                      <span id="cc2-3" class="text-red-500 font-bold">${data.charter.cc2[3]}</span>
                      <br>
                      <br>
                      <p>● 4</p>
                      <span id="cc2-4" class="text-red-500 font-bold">${data.charter.cc2[4]}</span>
                      <br>
                      <br>
                      <p>● 5</p>
                      <span id="cc2-5" class="text-red-500 font-bold">${data.charter.cc2[5]}</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow ">
                  <h1 class="font-medium">C. Citizen’s Charter Helpfulness (CC3)</h1>
                    <div class="pl-2 pt-2">
                      <p>● 1</p>
                      <span id="cc3-1" class="text-red-500 font-bold">${data.charter.cc3[1]}</span>
                      <br>
                      <br>
                      <p>● 2</p>
                      <span id="cc3-2" class="text-red-500 font-bold">${data.charter.cc3[2]}</span>
                      <br>
                      <br>
                      <p>● 3</p>
                      <span id="cc3-3" class="text-red-500 font-bold">${data.charter.cc3[3]}</span>
                      <br>
                      <br>
                      <p>● 4</p>
                      <span id="cc3-4" class="text-red-500 font-bold">${data.charter.cc3[4]}</span>
                    </div>
                </div>
              </div>
          </div>
          <div class="bg-white p-3 rounded-lg shadow mb-3">
            <h1><strong>V. Result count of SQD questions for FY <?= date('Y') ?></strong></h1>
            <div class="grid grid-cols-4 gap-4">
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD1 (Responsiveness)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd1[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd1[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd1[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd1[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd1[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd1['na']}</span>
                  </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD2 (Reliability)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd2[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd2[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd2[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd2[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd2[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd2['na']}</span>
                  </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD3 (Access & Facility)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd3[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd3[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd3[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd3[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd3[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd3['na']}</span>
                  </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD4 (Communication)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd4[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd4[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd4[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd4[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd4[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd4['na']}</span>
                  </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD5 (Costs)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd5[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd5[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd5[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd5[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd5[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd5['na']}</span>
                  </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD6 (Integrity)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd6[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd6[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd6[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd6[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd6[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd6['na']}</span>
                  </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD7 (Assurance)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd7[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd7[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd7[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd7[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd7[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd7['na']}</span>
                  </div>
              </div>
              <div class="bg-white p-4 rounded-lg shadow">
                <h1 class="font-medium">SQD8 (Outcome)</h1>
                  <div class="pl-2 pt-2">
                    <p>●  Strongly Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd8[5]}</span>
                    <br>
                    <br>
                    <p>● Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd8[4]}</span>
                    <br>
                    <br>
                    <p>● Neither Disagree nor Agree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd8[3]}</span>
                    <br>
                    <br>
                    <p>● Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd8[2]}</span>
                    <br>
                    <br>
                    <p>● Strongly Disagree</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd8[1]}</span>
                    <br>
                    <br>
                    <p>● Not Applicable</p>
                    <span class="text-red-500 font-bold">${data.sqd_breakdowns.sqd8['na']}</span>
                  </div>
              </div>
            </div>
          </div>
        </div>
      `;
        });
    });
    
  </script>

</body>

</html>