<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

// Language toggle
if (isset($_GET['lang'])) {
  $_SESSION['lang'] = $_GET['lang'];
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['feedback']['name'] = $_POST['name'] ?? '';
  $_SESSION['feedback']['date'] = $_POST['date'] ?? '';
  $_SESSION['feedback']['age'] = $_POST['age'] ?? '';
  $_SESSION['feedback']['sex'] = $_POST['sex'] ?? '';
  $_SESSION['feedback']['customer_type'] = $_POST['customer_type'] ?? '';
  $_SESSION['feedback']['service_availed'] = $_POST['service_availed'] ?? '';
  $_SESSION['feedback']['region'] = $_POST['region'] ?? '';

  $_SESSION['feedback_step'] = 'citizen-awareness';
  header('Location: /pages/feedback/citizen-awareness.php');
  exit;
}

// Language loader
$lang = $_SESSION['lang'] ?? 'en';
$labels = require __DIR__ . "/../../lang/$lang.php";

renderHead($labels['page_title_client_info'] ?? 'Client Information', true);
?>

<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">
  <!-- Header Section -->
  <header class="bg-emerald-950 shadow-md sticky top-0 z-10 p-2">
    <section class="max-w-6xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-4">
        <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-14 w-14 border rounded-full bg-white">
        <p class="text-xl md:text-3xl font-medium text-white">BESIMS</p>
      </div>
      <button id="menu-btn-mobile" class="md:hidden text-white focus:outline-none cursor-pointer">
        <img src="/assets/img/menu-icon.png" alt="Menu" class="h-6 w-6">
      </button>
      <nav id="menu-links" class="hidden md:flex flex-col md:flex-row justify-between items-center w-full md:w-auto px-4 py-2 md:p-0 bg-emerald-950 md:bg-transparent absolute md:static top-full left-0">
        <ul class="flex flex-col md:flex-row gap-4">
          <li><a href="/index.php" class="menu-link text-white hover:text-emerald-400"><?= $labels['nav_signin'] ?? 'Back to Sign in' ?></a></li>
          <li><a href="/pages/feedback-form.php" class="menu-link text-white hover:text-emerald-400"><?= $labels['nav_feedback'] ?? 'Feedback' ?></a></li>
          <li><a href="/pages/faqs.php" class="menu-link text-white hover:text-emerald-400"><?= $labels['nav_faqs'] ?? 'FAQs' ?></a></li>
          <li><a href="?lang=en" class="menu-link text-white hover:text-emerald-400 italic">🇺🇸 English</a></li>
          <li><a href="?lang=fil" class="menu-link text-white hover:text-emerald-400 italic">🇵🇭 Filipino</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <main class="flex-grow w-full px-4 pt-10">
    <form method="POST" class="bg-white border-2 border-emerald-800 rounded-lg p-6 md:p-8 mb-20 max-w-3xl mx-auto space-y-8 opacity-90">
      <section class="js-client-information-form space-y-6">
        <h4 class="text-center font-bold text-xl"><?= $labels['client_info_heading'] ?? 'Client Satisfaction Measurement (CSM) (2025)' ?></h4>
        <p class="text-lg text-white font-medium bg-emerald-700 w-fit px-4 py-2 rounded"><?= $labels['client_info_section_title'] ?? 'Client Information' ?></p>

        <div class="space-y-6">
          <!-- Pangalan -->
          <div>
            <label for="name" class="block font-medium text-sm"><?= $labels['label_name'] ?></label>
            <input type="text" id="name" name="name" placeholder="<?= $labels['placeholder_name'] ?>" class="w-full border-2 rounded-lg p-2">
          </div>

          <!-- Petsa -->
          <div>
            <label for="date" class="block font-medium text-sm"><?= $labels['label_date'] ?></label>
            <input type="date" id="date" name="date" value="<?= date('Y-m-d'); ?>" class="w-full border-2 rounded-lg p-2">
          </div>

          <!-- Edad -->
          <div>
            <label for="age" class="block font-medium text-sm"><?= $labels['label_age'] ?></label>
            <select id="age" name="age" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected><?= $labels['select_age'] ?></option>
              <option value="under-19"><?= $labels['age_under_19'] ?></option>
              <option value="20-34"><?= $labels['age_20_34'] ?></option>
              <option value="35-49"><?= $labels['age_35_49'] ?></option>
              <option value="50-64"><?= $labels['age_50_64'] ?></option>
              <option value="65-up"><?= $labels['age_65_up'] ?></option>
            </select>
          </div>

          <!-- Kasarian -->
          <div>
            <label for="sex" class="block font-medium text-sm"><?= $labels['label_sex'] ?></label>
            <select id="sex" name="sex" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected><?= $labels['select_sex'] ?></option>
              <option value="Female"><?= $labels['sex_female'] ?></option>
              <option value="Male"><?= $labels['sex_male'] ?></option>
            </select>
          </div>

          <!-- Uri ng Kliyente -->
          <div>
            <label for="customer_type" class="block font-medium text-sm"><?= $labels['label_customer_type'] ?></label>
           <select id="customer_type" name="customer_type" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected><?= $labels['select_customer_type'] ?></option>
              <option value="Business"><?= $labels['type_business'] ?></option>
              <option value="Citizen"><?= $labels['type_citizen'] ?></option>
              <option value="Government"><?= $labels['type_government'] ?></option>
            </select>
            <p class="text-sm pt-2"><?= $labels['customer_type_notes'] ?></p>
          </div>

          <!-- Serbisyong Natanggap -->
          <div>
            <label for="service_availed" class="block font-medium text-sm"><?= $labels['label_service'] ?></label>
            <select id="service_availed" name="service_availed" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected><?= $labels['select_service'] ?></option>
            </select>
          </div>

          <!-- Rehiyon -->
          <div>
            <label for="region" class="block font-medium text-sm"><?= $labels['label_region'] ?></label>
            <select id="region" name="region" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected><?= $labels['select_region'] ?></option>
              <?php
              $stmt = $pdo->query("SELECT id, code, name FROM regions ORDER BY code");
              while ($region = $stmt->fetch()) {
                echo "<option value=\"{$region['id']}\">{$region['code']} - {$region['name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Error Message -->
          <p id="feedback-form-error" class="text-red-600 font-bold text-sm"></p>

          <!-- Next Button -->
          <div class="text-center">
            <button type="submit" class="bg-emerald-800 text-white text-lg px-4 py-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600 mt-4">
              <?= $labels['continue_button'] ?>
            </button>
          </div>
        </div>
      </section>
    </form>
  </main>

  <?php include '../../includes/footer.php'; ?>
<script type="module" src="/assets/js/feedbackValidation-client-info.js"></script>
<script type="module" src="/assets/js/serviceAvailedOptions.js"></script>
<script type="module" src="/assets/js/app.js"></script>
</body>

</html>