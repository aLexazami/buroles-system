<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/src/styles.css" rel="stylesheet">
  <title>Burol Elementary School</title>
</head>

<body class="bg-gradient-to-b from-white to-emerald-800 h-screen">
  <!-- Header Section -->
  <header class=" bg-white shadow-md sticky-top-0 z-10">
    <section class="max-w-4xl mx-auto flex justify-between items-center">
      <div class="flex items-center ">
        <img src="../assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-20 ">
        <p class="text-4xl font-medium text-emerald-800 ml-5">
          Burol Elementary School
        </p>
      </div>
      <nav>
        <ul class="flex space-x-4 mr-3">
          <li><a href="/index.php" class="text-emerald-800 text-md hover:text-emerald-600 pr-10">Sign in</a></li>
          <li><a href="/pages/feedback-form.php" class="text-emerald-800 text-md hover:text-emerald-600">Feedback</a></li>
          <li><a href="/pages/faqs.php" class="text-emerald-800 text-md hover:text-emerald-600">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <!-- Thankyou Section-->
  <main class="max-w-4xl mx-auto px-4 pt-10 ">
    <section>
      <div class="bg-white shadow-md rounded-lg p-8 w-105 m-auto opacity-75 border-2 border-emerald-800">
        <h2 class="text-emerald-800 text-3xl font-bold text-center mb-5">Thank You!</h2>
        <p class="text-center text-lg">Your feedback has been successfully submitted.</p>
        <p class="text-center text-md mt-3">We appreciate your time and effort in helping us improve our services.</p>
        <div class="text-center mt-5">
          <a href="/pages/feedback-form.php" class="bg-emerald-800 text-white px-4 py-2 rounded hover:bg-emerald-600">Go Back to Feedback Form</a>
        </div>
      </div>
    </section>
  </main>

  <!--Footer Section-->
  <footer class="bg-emerald-950 absolute bottom-0 w-full">
    <section class="text-center py-3">
      <p class="text-white text-sm">
        Copyrights &copy; 2025. Burol Elementary School. All rights reserved.
      </p>
    </section>
  </footer>
</body>

</html>