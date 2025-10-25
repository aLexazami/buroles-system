<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/head.php';

renderHead('Reset Password', true);
?>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">
  <!-- Header -->
  <header class="bg-emerald-950 shadow-md  top-0 z-10 p-2">
    <section class="max-w-6xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-4">
        <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-14 w-14 border rounded-full bg-white">
        <p class="text-xl md:text-3xl font-medium text-white">BESIMS</p>
      </div>
    </section>
  </header>

  <!-- Main Content -->
  <main class="flex-grow w-full px-4 pt-10">
    <section class="flex justify-center py-10">
      <div class="w-full justify-center flex max-w-2xl xl:max-w-3xl">
        <form action="/controllers/send-reset.php" method="POST"
          class="bg-white shadow-md rounded-lg p-6 w-xl opacity-90 border-2 border-emerald-800">
          <p class="text-emerald-800 text-2xl text-center font-bold mb-6">Reset Password</p>

          <!-- Success/Error Message -->
          <?php if (isset($_SESSION['reset_message'])): ?>
            <div class="text-center mb-4">
              <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-2 rounded">
                <?= $_SESSION['reset_message']; ?>
              </div>
            </div>
            <?php unset($_SESSION['reset_message']); ?>
          <?php endif; ?>

          <!-- Email Input -->
          <div class="flex items-center gap-2 mb-5 border-2 rounded-lg px-3 py-2">
            <img src="/assets/img/email.png" class="h-5">
            <input type="email" name="email" placeholder="Enter your registered email"
              class="flex-1 h-12 p-2 border-l-2 focus:outline-none" required>
          </div>

          <!-- Submit Button -->
          <div class="text-center">
            <button type="submit"
              class="w-full bg-emerald-800 text-white p-2 rounded hover:bg-emerald-600 cursor-pointer">
              Send Reset Link
            </button>
          </div>

          <!-- Back to Login -->
          <div class="text-center mt-5">
            <a href="/index.php" class="text-emerald-800 hover:underline font-bold">
              Back to Login
            </a>
          </div>
        </form>
      </div>
    </section>
  </main>

  <!-- Footer -->
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