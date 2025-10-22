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

      <!-- 🎚️ Grade Level Filter -->
      <div class="mb-6 flex items-center gap-2">
        <!-- Filter Icon -->
        <img src="/assets/img/filter.png" alt="Filter Icon" class="w-5 h-5 object-contain" />

        <!-- Dropdown -->
        <div class="relative">
          <select id="gradeLevelFilter"
            class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150 ease-in-out">
            <option value="">All Levels</option>
            <?php
            $gradeLevels = $pdo->query("SELECT id, label FROM grade_levels ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($gradeLevels as $level):
            ?>
              <option value="<?= $level['id'] ?>"><?= htmlspecialchars($level['label']) ?></option>
            <?php endforeach; ?>
          </select>

          <!-- Dropdown Arrow Icon -->
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>


      <!-- 🧾 Student Table -->
      <div class="w-full overflow-x-auto">
        <table class="min-w-[640px] w-full table-auto bg-white rounded shadow overflow-hidden">
          <thead class="bg-emerald-600 text-white text-left text-xs sm:text-sm">
            <tr>
              <th class="py-2 px-4">Student</th>
              <th class="py-2 px-4">LRN</th>
              <th class="py-2 px-4">Gender</th>
              <th class="py-2 px-4">Grade & Section</th>
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