<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./src/styles.css" rel="stylesheet">
  <title>Burol Elementary School</title>
</head>

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
          <li><a href="/pages/feedback-form.php" class="menu-link text-white hover:text-emerald-400">Feedback</a></li>
          <li><a href="/pages/faqs.php" class="menu-link text-white hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <main class="flex-grow w-full px-4 pt-10">
    <section class="flex justify-center py-10">
      <div class="w-full justify-center flex max-w-2xl xl:max-w-3xl">
        <form action="/controllers/login.php" method="POST"
          class="bg-white shadow-md rounded-lg p-6 w-xl opacity-90 border-2 border-emerald-800">
          <p class="text-emerald-800 text-2xl text-center font-bold mb-6">SIGN IN</p>

          <!-- Error Message -->
          <?php if (isset($_SESSION['error_message'])): ?>
            <div class="text-red-500 text-center mb-4">
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
                <?= $_SESSION['error_message']; ?>
              </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
          <?php endif; ?>

          <!-- Username -->
          <div class="flex items-center gap-2 mb-5 border-2 rounded-lg px-3 py-2">
            <img src="./assets/img/username.png" class="h-5">
            <input type="text" id="username" name="username"
              class="flex-1 h-12 p-2 border-l-2 focus:outline-none"
              placeholder="Username" required>
          </div>

          <!-- Password -->
          <div class="relative mb-5">
            <div class="flex items-center gap-2 border-2 rounded-lg px-3 py-2">
              <img src="./assets/img/password.png" class="h-5">
              <input type="password" id="password" name="password"
                class="flex-1 h-12 p-2 border-l-2 focus:outline-none"
                placeholder="Password" required>
              <img src="/assets/img/eye-open.png" alt="Toggle visibility"
                class="absolute right-3 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
                data-toggle-password="password">
            </div>
          </div>

          <!-- Login Attempts -->
          <div class="text-center mb-5 max-w-md font-semibold  w-fit flex-start flex  py-2">
            <p>Login-attempt:
              <span class="text-red-700 pl-1">
                <?= isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0; ?>
              </span>
            </p>
          </div>



          <!-- Submit Button -->
          <div class="text-center">
            <button type="submit"
              class="w-full bg-emerald-800 text-white p-2 rounded hover:bg-emerald-600 cursor-pointer">
              Login
            </button>
          </div>

          <!-- Forgot Password -->
          <div class="text-center mt-5">
            <a href="/pages/reset-password.php" class="text-emerald-800 hover:underline font-bold">
              Reset/Forgot Password?
            </a>
          </div>
        </form>
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