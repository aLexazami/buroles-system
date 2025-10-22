<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

// 🔐 Role check
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  exit('Access denied');
}

// 📦 Validate class ID
$classId = $_GET['class_id'] ?? null;
if (!$classId || !ctype_digit($classId)) {
  http_response_code(400);
  exit('Invalid class ID');
}

// 🔍 Fetch class info
$stmt = $pdo->prepare("
  SELECT classes.id, classes.name,
         users.id AS adviser_id,
         users.first_name, users.middle_name, users.last_name, users.avatar_path
  FROM classes
  JOIN users ON classes.adviser_id = users.id
  WHERE classes.id = ?
");
$stmt->execute([$classId]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
  http_response_code(404);
  exit('Class not found');
}

renderHead('Admin');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php $adviserId = $class['adviser_id']; ?>
    <?php include('../../includes/side-nav-admin2.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <!-- Advisory Class Header -->
      <div class="flex items-center gap-4 mb-6 rounded p-5 bg-emerald-800 shadow text-white">
        <img src="<?= $class['avatar_path'] ?? '/assets/img/default-avatar.png' ?>" class="w-16 h-16 rounded-full border-2 border-white" alt="Avatar">
        <div>
          <h1 class="font-semibold text-md sm:text-lg md:text-xl leading-tight">
            <?= htmlspecialchars($class['name']) ?>
          </h1>
          <p class="text-sm sm:text-base">
            Adviser: <?= htmlspecialchars($class['last_name'] . ', ' . $class['first_name'] . ' ' . $class['middle_name']) ?>
          </p>
        </div>
      </div>

      <!-- Add Student Button -->
      <div class="flex justify-start mb-4">
        <button data-action="add-student" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
          <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
          <span>Add Student</span>
        </button>
      </div>

      <!-- 🧾 Advisory Student Table -->
      <input type="hidden" id="classId" value="<?= $class['id'] ?>">
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
          <tbody id="studentListTableBody" class="text-sm text-gray-800 text-left">
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