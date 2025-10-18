<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

$classId = $_GET['class_id'] ?? null;
if (!$classId) {
  header('Location: /pages/staff/class-advisory.php');
  exit;
}

// Fetch class info
$classStmt = $pdo->prepare("SELECT name, grade_level, section FROM classes WHERE id = ? AND adviser_id = ?");
$classStmt->execute([$classId, $_SESSION['user_id']]);
$classInfo = $classStmt->fetch(PDO::FETCH_ASSOC);

if (!$classInfo) {
  header('Location: /pages/staff/class-advisory.php');
  exit;
}

// Fetch students
$studentStmt = $pdo->prepare("SELECT id, first_name, last_name FROM students WHERE class_id = ?");
$studentStmt->execute([$classId]);
$students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

renderHead('Student List');
?>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <div id="flashContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 space-y-2 w-full max-w-sm sm:max-w-md"></div>
    <?php showFlash(); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="flex justify-between items-center mb-5">
        <div class="flex items-center gap-2">
          <img src="/assets/img/classroom.png" class="w-5 h-5" alt="Class icon">
          <h1 class="font-bold text-md sm:text-lg">
            <?= htmlspecialchars($classInfo['name']) ?> (Grade <?= $classInfo['grade_level'] ?> - Section <?= htmlspecialchars($classInfo['section']) ?>)
          </h1>
        </div>
        <a href="/pages/staff/class-advisory.php" class="text-sm text-emerald-700 hover:underline">← Back to Advisory List</a>
      </div>

      <div class="flex justify-start mb-4">
        <button data-action="add-student" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base">
          <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
          <span>Add Student</span>
        </button>
      </div>

      <?php if (count($students) === 0): ?>
        <div class="p-6 flex flex-col items-center justify-center text-gray-500 text-sm space-y-2">
          <img src="/assets/img/empty-student.png" alt="No students" class="h-16 w-16 opacity-50">
          <span>No students found in this class.</span>
        </div>
      <?php else: ?>
        <table class="min-w-full table-auto border-collapse bg-white rounded shadow overflow-hidden">
          <thead class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
            <tr>
              <th class="px-4 py-3">Student Name</th>
              <th class="px-4 py-3">Attendance</th>
              <th class="px-4 py-3">Grades</th>
              <th class="px-4 py-3">Feedback</th>
            </tr>
          </thead>
          <tbody class="text-sm text-gray-800">
            <?php foreach ($students as $s): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                <td class="px-4 py-2">
                  <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs"
                          onclick="openAttendanceModal(<?= $s['id'] ?>)">Mark</button>
                </td>
                <td class="px-4 py-2">
                  <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Enter</button>
                </td>
                <td class="px-4 py-2">
                  <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">Add</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>

  <?php
  include('../../includes/footer.php');
  include('../../includes/modals.php');
  ?>

  <!-- Pass class_id to modal via JS -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const addStudentBtn = document.querySelector('[data-action="add-student"]');
      const classId = <?= json_encode($classId) ?>;
      if (addStudentBtn) {
        addStudentBtn.addEventListener('click', () => {
          const form = document.getElementById('addStudentForm');
          if (form) {
            let hiddenInput = form.querySelector('input[name="class_id"]');
            if (!hiddenInput) {
              hiddenInput = document.createElement('input');
              hiddenInput.type = 'hidden';
              hiddenInput.name = 'class_id';
              form.appendChild(hiddenInput);
            }
            hiddenInput.value = classId;
          }
          toggleModal('addStudentModal', true);
        });
      }
    });
  </script>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>