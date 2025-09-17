<?php
$redirectUrl = $_GET['redirect'] ?? '/index.php';
$delaySeconds = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="refresh" content="<?= $delaySeconds ?>;url=<?= $redirectUrl ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Password Updated</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
      animation: fade-in 0.8s ease-out;
    }
  </style>
</head>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen ">
  <div class="bg-white shadow-lg border border-emerald-500 p-6 w-full text-center animate-fade-in">
    <p class="text-green-800 text-2xl font-medium">Password updated successfully! Redirecting...</p>
    <p class="text-sm text-gray-500 mt-2">You will be redirected shortly.</p>
    <div class="mt-6">
      <a href="<?= $redirectUrl ?>" class="text-sm text-emerald-600 hover:underline">Click here if you're not redirected</a>
    </div>
  </div>
</body>
</html>