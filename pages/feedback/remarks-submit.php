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

// Language loader
$lang = $_SESSION['lang'] ?? 'en';
$labels = require __DIR__ . "/../../lang/$lang.php";

renderHead($labels['page_title_remarks'] ?? 'Final Remarks', true);
?>

<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">
  <!-- Header Section -->
  <header class="bg-emerald-950 shadow-md sticky top-0 z-10 p-2">
    <section class="max-w-6xl mx-auto flex items-center justify-between">
      <!-- Logo + Title -->
      <div class="flex items-center gap-4">
        <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-14 w-14 border rounded-full bg-white">
        <p class="text-xl md:text-3xl font-medium text-white">BESIMS</p>
      </div>

      <!-- Mobile Menu Toggle -->
      <button id="menu-btn-mobile" class="md:hidden text-white focus:outline-none cursor-pointer">
        <img src="/assets/img/menu-icon.png" alt="Menu" class="h-6 w-6">
      </button>

      <!-- Navigation Links -->
      <nav id="menu-links" class="hidden md:flex flex-col md:flex-row justify-between items-center w-full md:w-auto px-4 py-2 md:p-0 bg-emerald-950 md:bg-transparent absolute md:static top-full left-0">
        <ul class="flex flex-col md:flex-row gap-4">
          <li><a href="/index.php" class="menu-link text-white hover:text-emerald-400">Back to Sign in</a></li>
          <li><a href="/pages/feedback-form.php" class="menu-link text-white hover:text-emerald-400">Feedback</a></li>
          <li><a href="/pages/faqs.php" class="menu-link text-white hover:text-emerald-400">FAQs</a></li>
          <li><a href="?lang=en" class="menu-link text-white hover:text-emerald-400 italic">🇺🇸 English</a></li>
          <li><a href="?lang=fil" class="menu-link text-white hover:text-emerald-400 italic">🇵🇭 Filipino</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <main class="flex-grow w-full px-4 py-12 flex items-center justify-center">
    <form method="POST" action="/controllers/submit-form.php" class="bg-white border-2 border-emerald-800 rounded-xl shadow-lg p-6 sm:p-10 max-w-2xl w-full space-y-8">
      <section class="js-remarks-form space-y-6">
        <h4 class="text-center font-bold text-xl sm:text-2xl">
          <?= $labels['client_info_heading'] ?? '109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)' ?>
        </h4>

        <h3 class="bg-emerald-800 w-fit px-4 py-2 text-lg text-white font-medium rounded">
          <?= $labels['remarks_section_title'] ?? 'Final Remarks' ?>
        </h3>

        <!-- Inject all previous feedback fields with CC mapping -->
        <?php foreach ($_SESSION['feedback'] ?? [] as $key => $value): ?>
          <?php
          if ($key === 'remarks') continue;
          $mappedKey = $key;
          if ($key === 'cc_1') $mappedKey = 'cc-1';
          if ($key === 'cc_2') $mappedKey = 'cc-2';
          if ($key === 'cc_3') $mappedKey = 'cc-3';
          ?>
          <input type="hidden" name="<?= htmlspecialchars($mappedKey) ?>" value="<?= htmlspecialchars($value) ?>">
        <?php endforeach; ?>

        <!-- Remarks textarea -->
        <div class="space-y-4">
          <label for="remarks" class="block font-medium text-sm md:text-base">
            <?= $labels['label_remarks'] ?? 'Do you have any suggestions or comments?' ?>
          </label>
          <textarea id="remarks" name="remarks" rows="5" class="w-full border-2 rounded-lg p-2 resize-none" placeholder="<?= $labels['placeholder_remarks'] ?? 'Write your comments here (optional)' ?>"></textarea>
        </div>

        <!-- Submit flag -->
        <input type="hidden" name="submit" value="true">

        <!-- Navigation buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6">
          <a href="/pages/feedback/client-satisfaction.php" class="bg-emerald-800 text-white px-6 py-2 rounded-md w-full sm:w-1/2 hover:bg-emerald-600 text-center">
            <?= $labels['back_button'] ?? 'Previous' ?>
          </a>
          <button type="submit" class="bg-emerald-800 text-white px-6 py-2 rounded-md w-full sm:w-1/2 hover:bg-emerald-600 cursor-pointer">
            <?= $labels['submit_button'] ?? 'Submit Feedback' ?>
          </button>
        </div>
      </section>
    </form>
  </main>

  <?php include '../../includes/footer.php'; ?>
  <script type="module" src="/assets/js/feedbackValidation-remarks.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>