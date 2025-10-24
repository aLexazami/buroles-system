<?php
session_start();
require_once __DIR__ . '/../../helpers/head.php';

// Optional: clear feedback session after submission
unset($_SESSION['feedback']);
unset($_SESSION['feedback_step']);

// Language toggle
if (isset($_GET['lang'])) {
  $_SESSION['lang'] = $_GET['lang'];
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Language loader
$lang = $_SESSION['lang'] ?? 'en';
$labels = require __DIR__ . "/../../lang/$lang.php";

renderHead($labels['page_title_thank_you'] ?? 'Thank You', true);
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
    <section class="bg-white border-2 border-emerald-800 rounded-xl shadow-lg p-6 sm:p-10 max-w-2xl w-full space-y-6 text-center">
      <h2 class="text-2xl sm:text-3xl font-bold text-emerald-800">
        <?= $labels['thank_you_heading'] ?? 'Thank you for your feedback!' ?>
      </h2>
      <p class="text-base sm:text-lg text-gray-700 leading-relaxed">
        <?= $labels['thank_you_message'] ?? 'Your responses have been recorded. We appreciate your time and insights in helping us improve our services.' ?>
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
        <a href="/index.php" class="bg-emerald-800 text-white px-6 py-2 rounded-md hover:bg-emerald-600 transition">
          <?= $labels['return_home'] ?? 'Return to Home' ?>
        </a>
        <a href="/pages/feedback/terms-agreement.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-300 transition">
          <?= $labels['submit_another'] ?? 'Submit Another Feedback' ?>
        </a>
      </div>
    </section>
  </main>

  <?php include '../../includes/footer.php'; ?>
  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>