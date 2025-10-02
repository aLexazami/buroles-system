<?php
http_response_code(500);
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Server Error', true);
?>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">

  <!-- Main Content -->
  <main class="flex-grow w-full px-4 pt-10">
    <section class="flex justify-center py-10">
      <div class="bg-white shadow-md rounded-lg p-6 border-2 border-red-700 max-w-xl w-full text-center">
        <h1 class="text-4xl font-bold text-red-700 mb-4">500</h1>
        <p class="text-lg text-gray-700 mb-6">Oops! Something went wrong on our end.</p>
        <a href="/" class="inline-block bg-red-700 text-white px-4 py-2 rounded hover:bg-red-600 transition">
          Return to Dashboard
        </a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <script type="module" src="/assets/js/app.js"></script>
</body>
</html>