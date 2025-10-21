<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

// 🔐 Role check
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  exit('Access denied');
}

// 🧑‍🏫 Validate adviser ID
$userId = $_GET['user_id'] ?? null;
if (!$userId || !ctype_digit($userId)) {
  http_response_code(400);
  exit('Invalid adviser ID');
}

// 🔍 Fetch adviser info
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

// 📅 Fetch all school years, prioritizing active ones
$schoolYears = $pdo->query("
  SELECT id, label, is_active
  FROM school_years
  ORDER BY is_active DESC, start_year DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 📅 Determine selected school year
$selectedYearId = $_GET['school_year_id'] ?? null;
if (!$selectedYearId) {
  // Auto-select most recent active year
  foreach ($schoolYears as $sy) {
    if ($sy['is_active']) {
      $selectedYearId = $sy['id'];
      break;
    }
  }
}

// 📚 Fetch advisory classes
$query = "
  SELECT classes.id, sy.label AS school_year,
         CONCAT(gl.label, ' - ', gs.section_label) AS name
  FROM classes
  JOIN grade_sections gs ON classes.grade_section_id = gs.id
  JOIN grade_levels gl ON gs.grade_level_id = gl.id
  JOIN school_years sy ON classes.school_year_id = sy.id
  WHERE classes.adviser_id = ?
";
$params = [$adviser['id']];

if ($selectedYearId && ctype_digit((string)$selectedYearId)) {
  $query .= " AND classes.school_year_id = ?";
  $params[] = $selectedYearId;
}

$query .= " ORDER BY sy.start_year DESC, gl.level ASC, gs.section_label ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$advisoryClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📦 Fetch grade levels and sections for modal
$gradeLevels = $pdo->query("SELECT id, label FROM grade_levels ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);
$sections = $pdo->query("SELECT id, grade_level_id, section_label FROM grade_sections WHERE is_active = 1 ORDER BY grade_level_id, section_label ASC")->fetchAll(PDO::FETCH_ASSOC);

renderHead('Admin');
?>

<body class=" bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin1.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <!-- 👤 Adviser Info -->
      <div class="flex items-center gap-4 mb-6 rounded p-5 bg-gradient-to-r from-emerald-800 to-teal-500  shadow text-white">
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
      <form method="GET" class="mb-6">
        <input type="hidden" name="user_id" value="<?= $adviser['id'] ?>">
        <label for="schoolYearFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by School Year</label>
        <select name="school_year_id" id="schoolYearFilter" class="w-full max-w-xs border rounded px-3 py-2 mb-2" onchange="this.form.submit()">
          <?php foreach ($schoolYears as $sy): ?>
            <option value="<?= $sy['id'] ?>" <?= ($selectedYearId == $sy['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($sy['label']) ?><?= !$sy['is_active'] ? ' (Inactive)' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <!-- 📂 Advisory Class Grid -->
      <div id="advisoryClassGrid" class="min-h-[100px]">
  <!-- Grid will be injected by JS -->
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