<?php
http_response_code(404);
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Page Not Found', true);
?>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">

  <!-- Main Content -->
  <main class="flex-grow w-full px-4 pt-10">
    <section class="flex justify-center py-10">
      <div class="bg-white shadow-md rounded-lg p-6 border-2 border-emerald-800 max-w-xl w-full text-center">
        <h1 class="text-4xl font-bold text-emerald-800 mb-4">404</h1>
        <p class="text-lg text-gray-700 mb-6">The page you're looking for doesn't exist.</p>
        <a href="/" class="inline-block bg-emerald-800 text-white px-4 py-2 rounded hover:bg-emerald-600 transition">
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