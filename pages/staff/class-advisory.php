<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

if ($_SESSION['role_slug'] !== 'staff') {
  http_response_code(403);
  exit('Access denied');
}

$userId = $_SESSION['user_id'];

// 📅 Fetch all school years
$schoolYears = $pdo->query("SELECT id, label, is_active FROM school_years ORDER BY is_active DESC, start_year DESC")->fetchAll(PDO::FETCH_ASSOC);

// 📅 Determine selected school year
$selectedYearId = $_GET['school_year_id'] ?? null;
if (!$selectedYearId) {
  foreach ($schoolYears as $sy) {
    if ($sy['is_active']) {
      $selectedYearId = $sy['id'];
      break;
    }
  }
}

// 🔍 Fetch selected school year label
$activeSY = null;
foreach ($schoolYears as $sy) {
  if ($sy['id'] == $selectedYearId) {
    $activeSY = $sy;
    break;
  }
}

// 🔍 Fetch advisory class for selected school year
$class = null;
if ($activeSY) {
  $stmt = $pdo->prepare("
    SELECT classes.id, classes.name, classes.school_year_id,
           users.first_name, users.middle_name, users.last_name, users.avatar_path,
           gl.label AS grade_label, gs.section_label
    FROM classes
    JOIN users ON classes.adviser_id = users.id
    JOIN grade_sections gs ON classes.grade_section_id = gs.id
    JOIN grade_levels gl ON gs.grade_level_id = gl.id
    WHERE classes.adviser_id = ? AND classes.school_year_id = ?
    LIMIT 1
  ");
  $stmt->execute([$userId, $activeSY['id']]);
  $class = $stmt->fetch(PDO::FETCH_ASSOC);
}

renderHead('Staff');
?>

<body class="bg-gray-100 min-h-screen flex flex-col" data-role="<?= htmlspecialchars($_SESSION['role_slug']) ?>">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">

      <!-- 🏫 School Year Header -->
      <div class="flex justify-center [font-family:'Times_New_Roman',Times,serif] items-center gap-4 mb-6 rounded p-5 bg-gradient-to-r from-emerald-800 to-teal-500 shadow text-white">
        <h1 class="font-semibold text-xl sm:text-2xl md:text-3xl leading-tight">
          <?= htmlspecialchars($activeSY['label'] ?? '—') ?>
        </h1>
      </div>


      <!-- 📅 School Year Filter -->
      <form method="GET" class="mb-6 flex items-center gap-2">
        <img src="/assets/img/calendar.png" alt="School Year Icon" class="w-5 h-5 object-contain" />
        <div class="relative">
          <select name="school_year_id" id="schoolYearFilter"
            class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150 ease-in-out"
            onchange="this.form.submit()">
            <?php foreach ($schoolYears as $sy): ?>
              <option value="<?= $sy['id'] ?>" <?= ($selectedYearId == $sy['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($sy['label']) ?><?= !$sy['is_active'] ? ' (Inactive)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </form>

      <?php if (!$activeSY): ?>
        <!-- 🚫 No Active School Year -->
        <div class="bg-white rounded shadow p-6 text-center text-gray-500">
          <img src="/assets/img/empty-school-year.png" alt="No active school year" class="w-16 h-16 mx-auto mb-4 opacity-50">
          <p class="text-sm sm:text-base">No school year selected or available.</p>
        </div>

      <?php elseif (!$class): ?>
        <!-- 🚫 No Advisory Class -->
        <div class="bg-white rounded shadow p-6 text-center text-gray-500">
          <img src="/assets/img/empty-class.png" alt="No advisory" class="w-16 h-16 mx-auto mb-4 opacity-50">
          <p class="text-sm sm:text-base">No advisory class assigned to you for <?= htmlspecialchars($activeSY['label']) ?>.</p>
        </div>

      <?php else: ?>
        <!-- ✅ Advisory Class Details -->
        <div class="flex items-center gap-4 mb-6 rounded p-5 bg-emerald-800 shadow text-white">
          <img src="<?= $class['avatar_path'] ?? '/assets/img/default-avatar.png' ?>" class="w-16 h-16 rounded-full border-2 border-white" alt="Adviser">
          <div>
            <h2 class="font-semibold text-md sm:text-lg md:text-xl leading-tight">
              <?= htmlspecialchars($class['grade_label'] . ' - ' . $class['section_label']) ?>
            </h2>
            <p class="text-sm sm:text-base">
              Adviser: <?= htmlspecialchars($class['last_name'] . ', ' . $class['first_name'] . ' ' . $class['middle_name']) ?>
            </p>
          </div>
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
      <?php endif; ?>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>