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
  $sqd = [];
  for ($i = 1; $i <= 8; $i++) {
    $key = "SQD$i";
    $sqd[$key] = $_POST[$key] ?? null;
  }

  if (in_array(null, $sqd, true)) {
    $_SESSION['feedback_error'] = 'Please answer all satisfaction questions.';
  } else {
    foreach ($sqd as $key => $value) {
      $_SESSION['feedback'][$key] = $value;
    }
    $_SESSION['feedback_step'] = 'remarks-submit';
    header('Location: /pages/feedback/remarks-submit.php');
    exit;
  }
}

// Language loader
$lang = $_SESSION['lang'] ?? 'en';
$labels = require __DIR__ . "/../../lang/$lang.php";

renderHead($labels['page_title_satisfaction'] ?? 'Client Satisfaction', true);
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
    <form method="POST" class="bg-white border-2 border-emerald-800 rounded-lg p-6 md:p-8 mb-20 max-w-4xl mx-auto space-y-8 opacity-90">
      <section class="js-client-satisfaction-form space-y-6">
        <h4 class="text-center font-bold text-xl">
          <?= $labels['client_info_heading'] ?? '109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)' ?>
        </h4>

        <h3 class="bg-emerald-800 w-fit px-4 py-2 text-lg text-white font-medium rounded">
          <?= $labels['satisfaction_section_title'] ?? 'Client Satisfaction' ?>
        </h3>

        <div class="space-y-6">
          <?php
          $questions = [
            1 => $labels['sqd1'] ?? 'SQD1 fallback',
            2 => $labels['sqd2'] ?? 'SQD2 fallback',
            3 => $labels['sqd3'] ?? 'SQD3 fallback',
            4 => $labels['sqd4'] ?? 'SQD4 fallback',
            5 => $labels['sqd5'] ?? 'SQD5 fallback',
            6 => $labels['sqd6'] ?? 'SQD6 fallback',
            7 => $labels['sqd7'] ?? 'SQD7 fallback',
            8 => $labels['sqd8'] ?? 'SQD8 fallback',
          ];

          $choices = [
            '5' => $labels['sqd_opt_5'] ?? '(5) Strongly Agree',
            '4' => $labels['sqd_opt_4'] ?? '(4) Agree',
            '3' => $labels['sqd_opt_3'] ?? '(3) Neither Agree or Disagree',
            '2' => $labels['sqd_opt_2'] ?? '(2) Disagree',
            '1' => $labels['sqd_opt_1'] ?? '(1) Strongly Disagree',
            'na' => $labels['sqd_opt_na'] ?? 'Not Applicable',
          ];

          foreach ($questions as $num => $text):
            $name = "SQD$num";
          ?>
            <div class="space-y-4">
              <label class="block font-medium text-sm md:text-base" for="<?= $name ?>">
                <i>SQD<?= $num ?> - <?= $text ?></i>
              </label>
              <div class="space-y-2">
                <?php foreach ($choices as $val => $label): ?>
                  <label class="block">
                    <input type="radio" name="<?= $name ?>" value="<?= $val ?>" class="mr-2"> <?= $label ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['feedback_error'])): ?>
          <p class="text-red-600 font-bold text-sm"><?= $_SESSION['feedback_error'] ?></p>
          <?php unset($_SESSION['feedback_error']); ?>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row gap-4 pt-6">
          <a href="<?= ($_SESSION['feedback']['citizen_charter_awareness'] ?? '') === 'yes' ? '/pages/feedback/citizen-charter.php' : '/pages/feedback/citizen-awareness.php' ?>" class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600 text-center">
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
  <script type="module" src="/assets/js/feedbackValidation-satisfaction.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>