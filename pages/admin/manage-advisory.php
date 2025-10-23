<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  exit('Access denied');
}

$userId = $_GET['user_id'] ?? null;
if (!$userId || !ctype_digit($userId)) {
  http_response_code(400);
  exit('Invalid adviser ID');
}

$stmt = $pdo->prepare("
  SELECT id, first_name, middle_name, last_name, avatar_path, email
  FROM users
  WHERE id = ? AND role_id = 1 AND is_archived = 0 AND is_locked = 0
");
$stmt->execute([$userId]);
$adviser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adviser) {
  http_response_code(404);
  exit('Adviser not found or inactive');
}

$schoolYears = $pdo->query("
  SELECT id, label, is_active
  FROM school_years
  ORDER BY is_active DESC, start_year DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 🎯 Always fetch the current active school year for header
$currentActiveSY = null;
foreach ($schoolYears as $sy) {
  if ($sy['is_active']) {
    $currentActiveSY = $sy;
    break;
  }
}

$selectedYearId = $_GET['school_year_id'] ?? null;
if (!$selectedYearId) {
  foreach ($schoolYears as $sy) {
    if ($sy['is_active']) {
      $selectedYearId = $sy['id'];
      break;
    }
  }
}

$gradeLevels = $pdo->query("SELECT id, label FROM grade_levels ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);
$sections = $pdo->query("SELECT id, grade_level_id, section_label FROM grade_sections WHERE is_active = 1 ORDER BY grade_level_id, section_label ASC")->fetchAll(PDO::FETCH_ASSOC);

renderHead('Admin');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin1.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">

      <!-- 🏫 School Year Header (Always shows current active year) -->
      <?php if ($currentActiveSY): ?>
        <div class="flex justify-center [font-family:'Times_New_Roman',Times,serif] items-center gap-4 mb-6 rounded p-5 bg-gradient-to-r from-emerald-800 to-teal-500 shadow text-white">
          <h1 class="font-semibold text-xl sm:text-2xl md:text-3xl leading-tight">
            <?= htmlspecialchars($currentActiveSY['label']) ?>
          </h1>
        </div>
      <?php endif; ?>

      <!-- 👤 Adviser Info -->
      <div class="flex items-center gap-4 mb-6 rounded p-5 bg-emerald-800 shadow text-white">
        <img src="<?= $adviser['avatar_path'] ?? '/assets/img/default-avatar.png' ?>" class="w-16 h-16 rounded-full border-2 border-emerald-400" alt="Avatar">
        <div>
          <h1 class="font-semibold text-md sm:text-lg md:text-xl leading-tight">
            <?= htmlspecialchars($adviser['last_name'] . ', ' . $adviser['first_name'] . ' ' . $adviser['middle_name']) ?>
          </h1>
          <p class="text-sm sm:text-base">
            <?= htmlspecialchars($adviser['email']) ?>
          </p>
        </div>
      </div>

      <!-- ➕ Create Button -->
      <div class="flex justify-start mb-4">
        <button data-action="create-advisory" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
          <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
          <span>Create Advisory Class</span>
        </button>
      </div>

      <!-- 📅 School Year Filter -->
      <form method="GET" class="mb-6 flex items-center gap-2">
        <input type="hidden" name="user_id" value="<?= $adviser['id'] ?>">
        <img src="/assets/img/calendar.png" alt="School Year Icon" class="w-5 h-5 object-contain" />
        <div class="relative">
          <select name="school_year_id" id="schoolYearFilter"
            class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition duration-150 ease-in-out">
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

      <!-- 📂 Advisory Class Grid (AJAX only) -->
      <div id="advisoryClassGrid" class="min-h-[100px] space-y-3">
        <!-- Grid will be injected by JS -->
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>
  <?php include('../../includes/modals.php'); ?>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>