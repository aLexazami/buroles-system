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
  if (!isset($_POST['agree'])) {
    exit('You must agree to continue.');
  }

  $_SESSION['agreed_to_terms'] = true;
  $_SESSION['feedback_step'] = 'client-info';
  header('Location: /pages/feedback/client-info.php');
  exit;
}

// Load language
$lang = $_SESSION['lang'] ?? 'en';
$labels = require __DIR__ . "/../../lang/$lang.php";

renderHead($labels['page_title_terms'], true);
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

  <!-- Main Content -->
  <main class="flex-grow w-full px-4 pt-10">
    <form method="POST" class="bg-white border-2 border-emerald-800 rounded-lg p-6 md:p-8 mb-20 max-w-4xl mx-auto space-y-10 opacity-90">
      <section class="space-y-6 text-sm leading-relaxed text-gray-800">
        <h1 class="text-2xl font-bold text-emerald-900"><?= $labels['privacy_title'] ?></h1>
        <p><?= $labels['privacy_intro'] ?></p>

        <h3 class="text-lg font-semibold text-emerald-800"><?= $labels['privacy_scope_title'] ?></h3>
        <p><?= $labels['privacy_scope'] ?></p>

        <h3 class="text-lg font-semibold text-emerald-800"><?= $labels['privacy_handling_title'] ?></h3>
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($labels['privacy_handling'] as $item): ?>
            <li><?= $item ?></li>
          <?php endforeach; ?>
        </ul>

        <h3 class="text-lg font-semibold text-emerald-800"><?= $labels['privacy_identity_title'] ?></h3>
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($labels['privacy_identity'] as $item): ?>
            <li><?= $item ?></li>
          <?php endforeach; ?>
        </ul>

        <h3 class="text-lg font-semibold text-emerald-800"><?= $labels['privacy_consent_title'] ?></h3>
        <p><?= $labels['privacy_consent'] ?></p>

        <label class="flex items-center gap-2 text-sm pt-4">
          <input type="checkbox" id="agree-checkbox" name="agree" class="accent-emerald-600">
          <?= $labels['agree_checkbox'] ?>
        </label>

        <div class="text-center">
          <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-600 disabled:opacity-50" id="continue-button" disabled>
            <?= $labels['continue_button'] ?>
          </button>
        </div>
      </section>
    </form>
  </main>

  <!-- Footer -->
  <?php include '../../includes/footer.php'; ?>

  <script type="module" src="/assets/js/app.js"></script>
  <script>
    const checkbox = document.getElementById('agree-checkbox');
    const button = document.getElementById('continue-button');
    checkbox.addEventListener('change', () => {
      button.disabled = !checkbox.checked;
    });
  </script>
</body>

</html>