<?php
require_once  __DIR__ . '/../../auth/session.php';
require_once  __DIR__ . '/../../config/database.php';
require_once  __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../helpers/head.php';
renderHead('Admin');
?>
<body class="bg-gray-200 min-h-screen flex flex-col">
  <!-- Header Section -->
  <?php include('../../includes/header.php'); ?>

  <!-- Feedback Summary Main Content Section -->
  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr] min-h-screen">
    <!-- Left Side Navigation Section -->
    <?php include '../../includes/side-nav-admin.php' ?>

    <!-- Right Side Context Section -->
    <section class="m-4">
      <div class="bg-emerald-300 p-2 flex justify-center items-center gap-2 mb-5">
        <img src="/assets/img/feedback-summary.png" class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-lg">Feedback Summary</h1>
      </div>

      <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-4 shadow-lg bg-white rounded-lg">
          <h1 class="text-lg text-center text-emerald-800 font-bold">Customer Type</h1>
          <div class="mt-10 divide-y divide-gray-200">
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">Business:</span>
              <span id="count-business" class="text-red-400 font-bold text-right"><?= $counts['Business'] ?? 0 ?></span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">Citizen:</span>
              <span id="count-citizen" class="text-red-400 font-bold text-right"><?= $counts['Citizen'] ?? 0 ?></span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">Government:</span>
              <span id="count-government" class="text-red-400 font-bold text-right"><?= $counts['Government'] ?? 0 ?></span>
            </div>
          </div>
        </div>

        <div class="p-4 shadow-lg bg-white rounded-lg">
          <h1 class="text-lg text-center text-emerald-800 font-bold">Customer Age</h1>
          <div class="mt-10 divide-y divide-gray-200">
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">19 - Under:</span>
              <span id="age-under-19" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">20 - 34:</span>
              <span id="age-20-34" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">35 - 49:</span>
              <span id="age-35-49" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">50 - 64:</span>
              <span id="age-50-64" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">65 - Higher:</span>
              <span id="age-65-up" class="text-red-400 font-bold text-right">0</span>
            </div>
          </div>
        </div>

        <div class="p-4 shadow-lg bg-white rounded-lg">
          <h1 class="text-lg text-center text-emerald-800 font-bold">Citizen Charter Awareness</h1>
          <div class="mt-10 divide-y divide-gray-200">
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">Yes:</span>
              <span id="awareness-yes" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">No:</span>
              <span id="awareness-no" class="text-red-400 font-bold text-right">0</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4 shadow-lg col-span-3 bg-white rounded-lg mt-4">
        <h1 class="text-lg text-center text-emerald-800 font-bold">Citizen Charter Awareness Response</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-10">
          <div class="divide-y divide-gray-200">
            <!-- CC1 Header with Toggle -->
            <div class="text-center font-bold pb-3 cursor-pointer" onclick="toggleLegend('cc1-legend')">
              <h1 class="text-emerald-800 hover:underline">CC1</h1>
            </div>
            <!-- CC1 Legend -->
            <div id="cc1-legend" class="bg-gray-100 p-3 rounded-lg text-sm text-left hidden">
              <p><strong>CC1: Which of the following best describes your awareness of a Citizen’s Charter?</strong></p>
              <ul class="list-disc pl-5 mt-2 space-y-1">
                <li><strong>1</strong> – I know what a Citizen’s Charter is and I saw this office’s Citizen’s Charter.</li>
                <li><strong>2</strong> – I know what a Citizen’s Charter is but I did not see this office’s Citizen’s Charter.</li>
                <li><strong>3</strong> – I learned of the Citizen’s Charter only when I saw this office’s Citizen’s Charter.</li>
                <li><strong>4</strong> – I do not know what a Citizen’s Charter is and I did not see one in this office.</li>
              </ul>
            </div>
            <!-- CC1 Breakdown -->
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">1. Fully aware and saw:</span>
              <span id="cc1-1" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">2. Aware but didn’t see:</span>
              <span id="cc1-2" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">3. Learned only by seeing:</span>
              <span id="cc1-3" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">4. Not aware at all:</span>
              <span id="cc1-4" class="text-red-400 font-bold text-right">0</span>
            </div>
          </div>

          <div class="divide-y divide-gray-200">
            <!-- CC2 Header with Toggle -->
            <div class="text-center font-bold pb-3 cursor-pointer" onclick="toggleLegend('cc2-legend')">
              <h1 class="text-emerald-800 hover:underline">CC2</h1>
            </div>
            <!-- CC2 Legend -->
            <div id="cc2-legend" class="bg-gray-100 p-3 rounded-lg text-sm text-left hidden">
              <p><strong>CC2: If aware of Citizen’s Charter (answered 1-3 in CC1), would you say that the CC of this office was …?</strong></p>
              <ul class="list-disc pl-5 mt-2 space-y-1">
                <li><strong>1</strong> – Easy to see</li>
                <li><strong>2</strong> – Somewhat easy to see</li>
                <li><strong>3</strong> – Difficult to see</li>
                <li><strong>4</strong> – Not visible at all</li>
                <li><strong>5</strong> – N/A</li>
              </ul>
            </div>
            <!-- CC2 Breakdown -->
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">1. Easy to see:</span>
              <span id="cc2-1" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">2. Somewhat easy:</span>
              <span id="cc2-2" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">3. Difficult to see:</span>
              <span id="cc2-3" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">4. Not visible:</span>
              <span id="cc2-4" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">5. N/A:</span>
              <span id="cc2-5" class="text-red-400 font-bold text-right">0</span>
            </div>
          </div>

          <div class="divide-y divide-gray-200">
            <!-- CC3 Header with Toggle -->
            <div class="text-center font-bold pb-3 cursor-pointer" onclick="toggleLegend('cc3-legend')">
              <h1 class="text-emerald-800 hover:underline">CC3</h1>
            </div>
            <!-- CC3 Legend -->
            <div id="cc3-legend" class="bg-gray-100 p-3 rounded-lg text-sm text-left hidden">
              <p><strong>CC3: If aware of Citizen’s Charter (answered 1-3 in CC1), how much did the CC help you in your transaction?</strong></p>
              <ul class="list-disc pl-5 mt-2 space-y-1">
                <li><strong>1</strong> – Helped very much</li>
                <li><strong>2</strong> – Somewhat helped</li>
                <li><strong>3</strong> – Did not help</li>
                <li><strong>4</strong> – N/A</li>
              </ul>
            </div>
            <!-- CC3 Breakdown -->
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">1. Helped very much:</span>
              <span id="cc3-1" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">2. Somewhat helped:</span>
              <span id="cc3-2" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">3. Did not help:</span>
              <span id="cc3-3" class="text-red-400 font-bold text-right">0</span>
            </div>
            <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
              <span class="font-medium">4. N/A:</span>
              <span id="cc3-4" class="text-red-400 font-bold text-right">0</span>
            </div>
          </div>
        </div>
      </div>
      <div class="p-4 shadow-lg col-span-3 bg-white rounded-lg mt-4">
        <h1 class="text-lg text-center text-emerald-800 font-bold">Client Satisfaction Matrix</h1>
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg p-4 mt-4">
          <table class="min-w-full table-auto border-collapse text-xs sm:text-sm">
            <thead class="bg-emerald-100 text-emerald-800">
              <tr>
                <th class="px-4 py-2 text-left">SQD Item</th>
                <th class="px-4 py-2 text-center">5<br>(Strongly Agree)</th>
                <th class="px-4 py-2 text-center">4<br>(Agree)</th>
                <th class="px-4 py-2 text-center">3<br>(Neither Agree or Disagree)</th>
                <th class="px-4 py-2 text-center">2<br>(Disagree)</th>
                <th class="px-4 py-2 text-center">1<br>(Strongly Disagree)</th>
                <th class="px-4 py-2 text-center">N/A</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <!-- SQD Rows -->
              <?php
              $items = [
                'SQD1 – Responsiveness',
                'SQD2 – Reliability',
                'SQD3 – Access and Facilities',
                'SQD4 – Communication',
                'SQD5 – Costs',
                'SQD6 – Integrity',
                'SQD7 – Assurance',
                'SQD8 – Outcome'
              ];
              foreach ($items as $index => $label):
                $i = $index + 1;
              ?>
                <tr>
                  <td class="px-4 py-2 font-medium"><?= $label ?></td>
                  <?php foreach (['5', '4', '3', '2', '1', 'na'] as $score): ?>
                    <td id="sqd<?= $i ?>-<?= $score ?>" class="text-center text-red-500 font-bold">0</td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-span-3 p-4 shadow-lg bg-white rounded-lg mt-4">
        <h1 class="text-lg text-center text-emerald-800 font-bold">Service Availed</h1>
        <div class="mt-10 divide-y divide-gray-200">
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Acceptance of Employment Application for Teacher I Position (Walk-in):</span>
            <span id="service-4" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Acceptance of Employment Application for Teacher I Position (Online):</span>
            <span id="service-5" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Borrowing of Learning Materials from the School Library/Learning Resource Center:</span>
            <span id="service-6" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Distribution of Printed Self Learning Modules in Distance Learning Modality:</span>
            <span id="service-7" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Enrollment (Walk-in):</span>
            <span id="service-2" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Enrollment (Online):</span>
            <span id="service-1" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Issuance of Requested Documents in Certified True Copy (CTC) and Photocopy (Walk-in):</span>
            <span id="service-8" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Issuance of Requested Documents in Certified True Copy (CTC) and Photocopy (Online):</span>
            <span id="service-9" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Issuance of School Clearance for different purposes:</span>
            <span id="service-10" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Issuance of School Forms, Certifications, and other School Permanent Records:</span>
            <span id="service-11" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Public Assistance (walk-in/phone call):</span>
            <span id="service-12" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Public Assistance (email/social media):</span>
            <span id="service-13" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Laboratory and School Inventory:</span>
            <span id="service-14" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">School Learning and Development:</span>
            <span id="service-15" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Receiving and releasing of communications and other documents:</span>
            <span id="service-16" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Request for Personnel Records for Teaching/Non-Teaching Personnel:</span>
            <span id="service-18" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Reservation Process for the Use of School Facilities:</span>
            <span id="service-17" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Issuance of Special Order for Service Credits and Certification of Compensatory Time Credits:</span>
            <span id="service-3" class="text-red-400 font-bold text-right">0</span>
          </div>
        </div>
      </div>
      <div class="col-span-4 p-4 shadow-lg bg-white rounded-lg mt-4">
        <h1 class="text-lg text-center text-emerald-800 font-bold">Region</h1>
        <div class="mt-10 divide-y divide-gray-200">
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region I - Ilocos Region:</span>
            <span id="region_i_ilocos_region" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region II - Cagayan Valley:</span>
            <span id="region_ii_cagayan_valley" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region III - Central Luzon:</span>
            <span id="region_iii_central_luzon" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region IV-A - Calabarzon:</span>
            <span id="region_iv-a_calabarzon" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">MIMAROPA - Southwestern Tagalog:</span>
            <span id="mimaropa_southwestern_tagalog" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region V - Bicol Region:</span>
            <span id="region_v_bicol_region" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region VI - Western Visayas:</span>
            <span id="region_vi_western_visayas" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region VII - Central Visayas:</span>
            <span id="region_vii_central_visayas" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region VIII - Eastern Visayas:</span>
            <span id="region_viii_eastern_visayas" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region IX - Zamboanga Peninsula:</span>
            <span id="region_ix_zamboanga_peninsula" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region X - Northern Mindanao:</span>
            <span id="region_x_northern_mindanao" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region XI - Davao Region:</span>
            <span id="region_xi_davao_region" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region XII - SOCCSKSARGEN:</span>
            <span id="region_xii_soccsksargen" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">Region XIII - Caraga:</span>
            <span id="region_xiii_caraga" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">NCR - National Capital Region:</span>
            <span id="ncr_national_capital_region" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">CAR - Cordillera Administrative Region:</span>
            <span id="car_cordillera_administrative_region" class="text-red-400 font-bold text-right">0</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 py-2 hover:bg-gray-100">
            <span class="font-medium">BARMM - Bangsamoro Autonomous Region:</span>
            <span id="barmm_bangsamoro_autonomous_region" class="text-red-400 font-bold text-right">0</span>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer Section -->
  <?php include '../../includes/footer.php' ?>

  <!-- Scripts -->
  <script src="/assets/js/update-sumarry.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>