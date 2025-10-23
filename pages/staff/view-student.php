<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';
require_once __DIR__ . '/../../helpers/flash.php';

if ($_SESSION['role_slug'] !== 'staff') {
  http_response_code(403);
  exit('Access denied');
}

$staffId = $_SESSION['user_id'];
$studentId = $_GET['id'] ?? null;
$classId = $_GET['class_id'] ?? null;

if (!$studentId || !$classId || !ctype_digit($studentId) || !ctype_digit($classId)) {
  setFlash('error', 'Missing or invalid student or class ID.');
  header('Location: /pages/staff/class-advisory.php');
  exit;
}

// 🔐 Validate that this class belongs to the logged-in staff
$stmt = $pdo->prepare("SELECT id FROM classes WHERE id = ? AND adviser_id = ?");
$stmt->execute([$classId, $staffId]);
$validClass = $stmt->fetchColumn();

if (!$validClass) {
  http_response_code(403);
  exit('You are not authorized to view this student.');
}

// 🧠 Fetch student details
$stmt = $pdo->prepare("
  SELECT s.*, 
         g.name AS guardian_name, 
         g.relationship, 
         g.contact_number AS guardian_contact, 
         g.email AS guardian_email,
         gl.label AS level_label, 
         gs.section_label, 
         e.school_year_id, 
         e.enrollment_date, 
         e.previous_school
  FROM students s
  LEFT JOIN guardians g ON s.id = g.student_id
  LEFT JOIN enrollments e ON s.id = e.student_id AND e.class_id = ?
  LEFT JOIN grade_sections gs ON e.grade_section_id = gs.id
  LEFT JOIN grade_levels gl ON gs.grade_level_id = gl.id
  WHERE s.id = ?
");
$stmt->execute([$classId, $studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
  setFlash('error', 'Student not found or not enrolled in your advisory class.');
  header('Location: /pages/staff/class-advisory.php');
  exit;
}

renderHead('Staff');
?>

<body class="bg-gray-100 min-h-screen flex flex-col" data-role="staff">
  <?php include('../../includes/header.php'); ?>
  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-staff1.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <!-- 🧑‍🎓 Header -->
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/student.png" class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-base sm:text-lg md:text-xl">Student Profile</h1>
      </div>

      <!-- 🧾 Profile Card -->
      <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <!-- Photo -->
        <?php if (!empty($student['photo_path'])): ?>
          <div class="flex justify-center">
            <img src="<?= $student['photo_path'] ?>" alt="Student Photo" class="w-32 h-32 object-cover rounded-full border shadow">
          </div>
        <?php endif; ?>

        <!-- Personal Info -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-2">Personal Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-800">
            <div><strong>Full Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']) ?></div>
            <div><strong>LRN:</strong> <?= htmlspecialchars($student['lrn']) ?></div>
            <div><strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?></div>
            <div><strong>Date of Birth:</strong> <?= htmlspecialchars($student['dob']) ?></div>
            <div><strong>Nationality:</strong> <?= htmlspecialchars($student['nationality']) ?></div>
            <div><strong>Contact Number:</strong> <?= htmlspecialchars($student['contact_number']) ?></div>
            <div class="sm:col-span-2"><strong>Address:</strong> <?= htmlspecialchars($student['address']) ?></div>
            <div><strong>Barangay:</strong> <?= htmlspecialchars($student['barangay']) ?></div>
          </div>
        </div>

        <!-- Enrollment Info -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-2">Enrollment Details</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-800">
            <div><strong>Grade Level:</strong> <?= htmlspecialchars($student['level_label']) ?></div>
            <div><strong>Section:</strong> <?= htmlspecialchars($student['section_label']) ?></div>
            <div><strong>School Year ID:</strong> <?= htmlspecialchars($student['school_year_id']) ?></div>
            <div><strong>Enrollment Date:</strong> <?= htmlspecialchars($student['enrollment_date']) ?></div>
            <div class="sm:col-span-2"><strong>Previous School:</strong> <?= htmlspecialchars($student['previous_school']) ?></div>
          </div>
        </div>

        <!-- Guardian Info -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-2">Guardian Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-800">
            <div><strong>Name:</strong> <?= htmlspecialchars($student['guardian_name']) ?></div>
            <div><strong>Relationship:</strong> <?= htmlspecialchars($student['relationship']) ?></div>
            <div><strong>Contact:</strong> <?= htmlspecialchars($student['guardian_contact']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($student['guardian_email']) ?></div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>