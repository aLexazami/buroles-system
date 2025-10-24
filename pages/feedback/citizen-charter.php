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
  $cc1 = $_POST['cc-1'] ?? null;
  $cc2 = $_POST['cc-2'] ?? null;
  $cc3 = $_POST['cc-3'] ?? null;

  if (!$cc1 || !$cc2 || !$cc3) {
    $_SESSION['feedback_error'] = 'Please answer all questions.';
  } else {
    $_SESSION['feedback']['cc_1'] = $cc1;
    $_SESSION['feedback']['cc_2'] = $cc2;
    $_SESSION['feedback']['cc_3'] = $cc3;
    $_SESSION['feedback_step'] = 'service-quality';
    header('Location: /pages/feedback/client-satisfaction.php');
    exit;
  }
}

// Language loader
$lang = $_SESSION['lang'] ?? 'en';
$labels = require __DIR__ . "/../../lang/$lang.php";

renderHead($labels['page_title_charter'] ?? 'Citizen Charter Details', true);
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
      <!-- Citizen Charter 2 Form Section -->
      <section class="js-citizen-charter-form p-5 space-y-6">
        <h4 class="text-center font-bold text-xl">
          <?= $labels['client_info_heading'] ?? '109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)' ?>
        </h4>

        <h3 class="text-lg font-medium text-white bg-emerald-800 w-fit px-4 py-2 rounded">
          <?= $labels['charter_section_title'] ?? "Citizen's Charter" ?>
        </h3>

        <!-- CC1 -->
        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base" for="cc-1">
            <i><?= $labels['cc1_question'] ?></i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="cc-1" value="1" class="mr-2"><?= $labels['cc1_opt_1'] ?></label>
            <label class="block"><input type="radio" name="cc-1" value="2" class="mr-2"><?= $labels['cc1_opt_2'] ?></label>
            <label class="block"><input type="radio" name="cc-1" value="3" class="mr-2"><?= $labels['cc1_opt_3'] ?></label>
            <label class="block"><input type="radio" name="cc-1" value="4" class="mr-2"><?= $labels['cc1_opt_4'] ?></label>
          </div>
        </div>

        <!-- CC2 -->
        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base" for="cc-2">
            <i><?= $labels['cc2_question'] ?></i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="cc-2" value="1" class="mr-2"><?= $labels['cc2_opt_1'] ?></label>
            <label class="block"><input type="radio" name="cc-2" value="2" class="mr-2"><?= $labels['cc2_opt_2'] ?></label>
            <label class="block"><input type="radio" name="cc-2" value="3" class="mr-2"><?= $labels['cc2_opt_3'] ?></label>
            <label class="block"><input type="radio" name="cc-2" value="4" class="mr-2"><?= $labels['cc2_opt_4'] ?></label>
            <label class="block"><input type="radio" name="cc-2" value="5" class="mr-2"><?= $labels['cc2_opt_5'] ?></label>
          </div>
        </div>

        <!-- CC3 -->
        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base" for="cc-3">
            <i><?= $labels['cc3_question'] ?></i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="cc-3" value="1" class="mr-2"><?= $labels['cc3_opt_1'] ?></label>
            <label class="block"><input type="radio" name="cc-3" value="2" class="mr-2"><?= $labels['cc3_opt_2'] ?></label>
            <label class="block"><input type="radio" name="cc-3" value="3" class="mr-2"><?= $labels['cc3_opt_3'] ?></label>
            <label class="block"><input type="radio" name="cc-3" value="4" class="mr-2"><?= $labels['cc3_opt_4'] ?></label>
          </div>
        </div>

        <!-- Error Message -->
        <?php if (isset($_SESSION['feedback_error'])): ?>
          <p id="feedback-form-error3" class="text-red-600 font-bold text-sm">
            <?= $_SESSION['feedback_error'] ?? '' ?>
          </p>
          <?php unset($_SESSION['feedback_error']); ?>
        <?php endif; ?>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-4">
          <a href="/pages/feedback/citizen-awareness.php" class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600 text-center">
            <?= $labels['back_button'] ?? 'Previous' ?>
          </a>
          <button type="submit" class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600">
            <?= $labels['continue_button'] ?? 'Next' ?>
          </button>
        </div>
      </section>
    </form>
  </main>

  <?php include '../../includes/footer.php'; ?>
  <script type="module" src="/assets/js/feedbackValidation-charter.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>