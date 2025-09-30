<?php
require_once __DIR__ . '/../helpers/head.php';

$redirectUrl = $_GET['redirect'] ?? '/index.php';
$delaySeconds = 3;
renderHead('Password Updated', true);
?>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col">

  <!-- Full-width redirect message at top -->
  <div class="w-full bg-white shadow-md border-b border-emerald-500 px-4 py-6 text-center animate-fade-in">
    <p class="text-green-800 text-2xl font-medium">Password updated successfully! Redirecting...</p>
    <p class="text-sm text-gray-500 mt-2">You will be redirected shortly.</p>
    <div class="mt-4">
      <a href="<?= $redirectUrl ?>" class="text-sm text-emerald-600 hover:underline">Click here if you're not redirected</a>
    </div>
  </div>

  <!-- Optional filler or branding content -->
  <div class="flex-grow flex items-center justify-center px-4">
    <!-- You can add a logo or animation here if desired -->
  </div>

</body>
</html>