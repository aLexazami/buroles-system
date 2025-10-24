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
  $awareness = $_POST['yes_no'] ?? null;

  if (!$awareness) {
    $_SESSION['feedback_error'] = 'Please select an option.';
  } else {
    $_SESSION['feedback']['citizen_charter_awareness'] = $awareness;

    if ($awareness === 'yes') {
      $_SESSION['feedback_step'] = 'citizen-charter';
      header('Location: /pages/feedback/citizen-charter.php');
    } else {
      $_SESSION['feedback_step'] = 'client-satisfaction';
      header('Location: /pages/feedback/client-satisfaction.php');
    }
    exit;
  }
}

// Language loader
$lang = $_SESSION['lang'] ?? 'en';
$labels = require __DIR__ . "/../../lang/$lang.php";

renderHead($labels['page_title_awareness'] ?? 'Citizen Awareness', true);
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

  <main class="flex-grow w-full px-4 pt-10">
    <form method="POST" class="bg-white border-2 border-emerald-800 rounded-lg p-6 md:p-8 mb-20 max-w-3xl mx-auto space-y-8 opacity-90">
      <!-- Citizen Charter Awareness Form Section -->
      <section class="js-citizen-awareness-form p-5 space-y-6">
        <h4 class="text-center font-bold text-xl">
          <?= $labels['client_info_heading'] ?? '109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)' ?>
        </h4>

        <h3 class="text-lg font-medium text-white bg-emerald-700 w-fit px-4 py-2 rounded">
          <?= $labels['awareness_section_title'] ?? "Citizen's Charter" ?>
        </h3>

        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base">
            <i><?= $labels['label_charter_awareness'] ?? "Are you aware of the Citizen's Charter — document of services and requirements?" ?></i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="yes_no" value="yes" class="mr-2"> <?= $labels['yes'] ?? 'Yes' ?></label>
            <label class="block"><input type="radio" name="yes_no" value="no" class="mr-2"> <?= $labels['no'] ?? 'No' ?></label>
          </div>
          <?php if (isset($_SESSION['feedback_error'])): ?>
            <p id="feedback-form-error2" class="text-red-600 font-bold text-sm">
              <?= $_SESSION['feedback_error'] ?? '' ?>
            </p>
            <?php unset($_SESSION['feedback_error']); ?>
          <?php endif; ?>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 pt-4">
          <a href="/pages/feedback/client-info.php" class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600 text-center">
            <?= $labels['back_button'] ?? 'Previous' ?>
          </a>
          <button type="submit" class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600 cursor-pointer">
            <?= $labels['continue_button'] ?? 'Next' ?>
          </button>
        </div>
      </section>
    </form>
  </main>

  <?php include '../../includes/footer.php'; ?>
  <script type="module" src="/assets/js/feedbackValidation-awareness.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>