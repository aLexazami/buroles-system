<?php
require_once __DIR__ . '/../helpers/head.php';

$redirectUrl = $_GET['redirect'] ?? '/index.php';
$delaySeconds = 3;

// Escape the full refresh string
$metaRefresh = "{$delaySeconds};url=" . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8');

renderHead('Password Updated', true, $metaRefresh);
?>

<body
  class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col"
  data-redirect-url="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>"
  data-delay-seconds="<?= $delaySeconds ?>"
>

  <!-- Full-width redirect message at top -->
  <div class="container mx-auto px-4">
    <div class="w-full bg-white shadow-md border-b border-emerald-500 px-4 sm:px-6 md:px-8 py-6 text-center animate-fade-in">
      <p class="text-green-800 text-xl sm:text-2xl font-medium">
        Password updated successfully! Redirecting...</p>
      <p class="text-sm text-gray-500 mt-2">
        You will be redirected in <span id="countdown" class="font-semibold text-emerald-700"><?= $delaySeconds ?></span> seconds.
      </p>
      <div class="mt-4 w-full max-w-sm mx-auto bg-gray-200 rounded-full overflow-hidden">
        <div id="progressBar" class="h-2 bg-emerald-500 transition-all duration-1000 w-0"></div>
      </div>
      <div class="mt-4">
        <a href="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-sm text-emerald-600 hover:underline">
          Click here if you're not redirected
        </a>
      </div>
    </div>
  </div>>
  
<script type="module" src="/assets/js/app.js"></script>
</body>

</html>