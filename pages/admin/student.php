<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';
require_once __DIR__ . '/../../helpers/flash.php';

renderHead('Admin');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin.php'); ?>
     <?php showFlash() ?>

    <section class="p-4 sm:p-6 md:p-8">
      <!-- 🧑‍🎓 Page Header -->
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/student.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Student Icon">
        <h1 class="font-bold text-base sm:text-lg md:text-xl">Student Management</h1>
      </div>

      <!-- ➕ Add Student Button -->
      <div class="flex justify-start mb-4">
        <a href="/pages/admin/create-student.php" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
          <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
          <span>Add Student</span>
        </a>
      </div>

      <!-- 🧾 Student Table -->
      <div class="w-full overflow-x-auto">
        <table class="min-w-[640px] w-full table-auto bg-white rounded shadow overflow-hidden">
          <thead class="bg-emerald-600 text-white text-left text-xs sm:text-sm">
            <tr>
              <th class="py-2 px-4">Student</th>
              <th class="py-2 px-4">LRN</th>
              <th class="py-2 px-4">Gender</th>
              <th class="py-2 px-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="studentTableBody" class="text-sm text-gray-800 text-left">
            <!-- Rows will be injected by JS -->
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <?php
  include('../../includes/footer.php');
  include('../../includes/modals.php');
  ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>