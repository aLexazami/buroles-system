<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/src/styles.css" rel="stylesheet">
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
  <main class="flex-grow max-w-4xl mx-auto px-4 py-10">
    <section>
      <div class="bg-white shadow-md rounded-lg p-6 md:p-8 border-2 border-emerald-800 opacity-90 space-y-4 text-center">
        <h2 class="text-emerald-800 text-2xl md:text-3xl font-bold">Thank You!</h2>
        <p class="text-base md:text-lg">Your feedback has been successfully submitted.</p>
        <p class="text-sm md:text-base">We appreciate your time and effort in helping us improve our services.</p>
        <div class="pt-4">
          <a href="/pages/feedback-form.php"
            class="inline-block bg-emerald-800 text-white px-4 py-2 rounded hover:bg-emerald-600">
            Go Back to Feedback Form
          </a>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer Section -->
  <footer class="bg-emerald-950 w-full mt-auto">
    <section class="text-center py-3">
      <p class="text-white text-xs md:text-sm">
        Copyrights &copy; 2025. Burol Elementary School. All rights reserved.
      </p>
    </section>
  </footer>
</body>

</html>