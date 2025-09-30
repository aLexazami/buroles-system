<?php
require_once __DIR__ . '/../helpers/head.php';
renderHead('FAQs', true);
?>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col ">
  <!-- Header Section -->
  <header class=" bg-emerald-950 shadow-md sticky-top-0 z-10 p-1">
    <section class="max-w-4xl mx-auto flex justify-between items-center">
      <div class="flex items-center ">
        <img src="../assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-20 border rounded-full bg-white">
        <p class="text-3xl font-medium text-white ml-5">
          Burol Elementary School
        </p>
      </div>
      <nav>
        <ul class="flex space-x-4 mr-3">
          <li><a href="../index.php" class="text-white text-md hover:text-emerald-400 pr-10">Sign in</a></li>
          <li><a href="/pages/feedback-form.php" class="text-white text-md hover:text-emerald-400">Feedback</a></li>
          <li><a href="/pages/faqs.php" class="text-white text-md hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <!-- FAQs Section -->
   <main  class="max-w-4xl mx-auto px-4 pt-10">
      <div class="bg-white p-8">
        <h1 class="text-4xl font-medium">Welcome to FAQs</h1>
      </div>
   </main>


  <!--Footer Section-->
 <?php include '../includes/footer.php'?>

  <script src="/assets/js/feedbackFormNavigation.js"></script>
  <script src="/assets/js/serviceAvailedOptions.js"></script>
</body>
</html>