<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('FAQs', true);
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
      <nav id="menu-links" class="hidden md:flex flex-col md:flex-row gap-4 text-sm md:text-base bg-emerald-950 md:bg-transparent absolute md:static top-full left-0 w-full md:w-auto px-4 py-2 md:p-0">
        <ul class="flex flex-col md:flex-row gap-4">
          <li><a href="/index.php" class="menu-link text-white hover:text-emerald-400">Sign in</a></li>
          <li><a href="/pages/feedback/terms-agreement.php" class="menu-link text-white hover:text-emerald-400">Feedback</a></li>
          <li><a href="/pages/faqs.php" class="menu-link text-white hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <main class="flex-grow w-full px-4 pt-10 pb-20">
    <section class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6 border-2 border-emerald-800 opacity-90">
      <h1 class="text-emerald-800 text-2xl font-bold mb-6 text-center">Frequently Asked Questions</h1>

      <div class="space-y-6 text-gray-700 text-sm md:text-base">
        <div>
          <h2 class="font-semibold text-emerald-700">1. How do I log in to the BESIMS dashboard?</h2>
          <p>Visit the <a href="/index.php" class="text-emerald-600 underline">Sign in</a> page and enter your assigned username and password. If you’ve forgotten your credentials, use the reset link provided.</p>
        </div>

        <div>
          <h2 class="font-semibold text-emerald-700">2. Who can submit feedback?</h2>
          <p>Any stakeholder—students, parents, teachers, or staff—can submit feedback using the <a href="/pages/feedback-form.php" class="text-emerald-600 underline">Feedback Form</a>.</p>
        </div>

        <div>
          <h2 class="font-semibold text-emerald-700">3. How is my feedback used?</h2>
          <p>Feedback is reviewed by the school administration and used to improve services, facilities, and communication. All responses are confidential and anonymized.</p>
        </div>

        <div>
          <h2 class="font-semibold text-emerald-700">4. What should I do if I encounter a technical issue?</h2>
          <p>Please report any issues to the school’s ICT coordinator or email the Burol Dev Team at <a href="mailto:support@burol.edu.ph" class="text-emerald-600 underline">support@burol.edu.ph</a>.</p>
        </div>
        <div>
          <h2 class="font-semibold text-emerald-700">5. What should I do if I forgot my password?</h2>
          <p>If you’ve forgotten your password, visit the <a href="/pages/reset-password.php" class="text-emerald-600 underline">Reset Password</a> page. Enter your username and follow the instructions to verify your identity and create a new password. If you need help, contact the Burol Dev Team at <a href="mailto:support@burol.edu.ph" class="text-emerald-600 underline">support@burol.edu.ph</a>.</p>
        </div>

      </div>
    </section>
  </main>

  <!-- Footer Section -->
  <footer class="bg-emerald-950 w-full mt-auto">
    <section class="text-center py-3 px-4">
      <p class="text-white text-xs md:text-sm">
        &copy; 2025 Burol Elementary School. All rights reserved.
      </p>
    </section>
  </footer>

  <script type="module" src="/assets/js/app.js"></script>
</body>

</html>