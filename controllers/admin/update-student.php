<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/validate.php';
require_once __DIR__ . '/../../helpers/flash.php';

$form_errors = [];
$form_data = $_POST;
$id = $_GET['id'] ?? null;

if (!$id) {
  setFlash('error', 'Missing student ID.');
  header('Location: /pages/admin/student.php');
  exit;
}

try {
  // 🧍 Student Info
  try { $lrn = validateLRN($pdo, $_POST['lrn'], $id); } catch (Exception $e) { $form_errors['lrn'] = $e->getMessage(); }
  try { $first_name = validateRequired($_POST['first_name'], 'First Name'); } catch (Exception $e) { $form_errors['first_name'] = $e->getMessage(); }
  $middle_name = sanitize($_POST['middle_name'] ?? '');
  try { $last_name = validateRequired($_POST['last_name'], 'Last Name'); } catch (Exception $e) { $form_errors['last_name'] = $e->getMessage(); }
  try { $dob = validateDate($_POST['dob'], 'Date of Birth'); } catch (Exception $e) { $form_errors['dob'] = $e->getMessage(); }
  try { $gender = validateEnum($_POST['gender'], ['male', 'female', 'other'], 'Gender'); } catch (Exception $e) { $form_errors['gender'] = $e->getMessage(); }

  $nationality = sanitize($_POST['nationality'] ?? '');
  $address = sanitize($_POST['address'] ?? '');
  $barangay = sanitize($_POST['barangay'] ?? '');
  try { $contact_number = validateContactNumber($_POST['contact_number'] ?? ''); } catch (Exception $e) { $form_errors['contact_number'] = $e->getMessage(); }

  // 👨‍👩‍👧 Guardian Info
  try { $guardian_name = validateRequired($_POST['guardian_name'], 'Guardian Name'); } catch (Exception $e) { $form_errors['guardian_name'] = $e->getMessage(); }
  try { $relationship = validateRequired($_POST['relationship'], 'Relationship'); } catch (Exception $e) { $form_errors['relationship'] = $e->getMessage(); }
  try { $guardian_contact = validateContactNumber($_POST['guardian_contact'] ?? ''); } catch (Exception $e) { $form_errors['guardian_contact'] = $e->getMessage(); }
  try { $guardian_email = validateEmail($_POST['guardian_email'] ?? ''); } catch (Exception $e) { $form_errors['guardian_email'] = $e->getMessage(); }

  $occupation = sanitize($_POST['occupation'] ?? '');
  $employer = sanitize($_POST['employer'] ?? '');
  $is_emergency_contact = isset($_POST['is_emergency_contact']) ? 1 : 0;

  // 🏫 Enrollment Info
  try {
    if (empty($_POST['school_year_id'])) throw new Exception('School Year is required.');
    $school_year_id = $_POST['school_year_id'];
  } catch (Exception $e) {
    $form_errors['school_year_id'] = $e->getMessage();
  }

  try {
    if (empty($_POST['grade_section_id'])) throw new Exception('Section is required.');
    $grade_section_id = $_POST['grade_section_id'];
  } catch (Exception $e) {
    $form_errors['grade_section_id'] = $e->getMessage();
  }

  try { $enrollment_date = validateDate($_POST['enrollment_date'], 'Enrollment Date'); } catch (Exception $e) { $form_errors['enrollment_date'] = $e->getMessage(); }
  $previous_school = sanitize($_POST['previous_school'] ?? '');

  // 🖼️ Photo Upload
  $photo_path = null;
  if (!empty($_FILES['photo']['name'])) {
    try {
      $filename = validateFileUpload($_FILES['photo']);
      $upload_dir = __DIR__ . '/../../assets/img/students/';
      if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

      $ext = pathinfo($filename, PATHINFO_EXTENSION);
      $unique_name = 'student_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
      $target_path = $upload_dir . $unique_name;

      if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
        $photo_path = '/assets/img/students/' . $unique_name;
      }
    } catch (Exception $e) {
      $form_errors['photo'] = $e->getMessage();
    }
  }

  // 🛑 If any validation errors, redirect back
  if (!empty($form_errors)) {
    setFlash('error', 'Please fix the highlighted errors.');
    setFlashData('form_errors', $form_errors);
    setFlashData('form_data', $form_data);
    header("Location: /pages/admin/edit-student.php?id=$id");
    exit;
  }

  // 🔍 Fetch current data for change detection
  $stmt = $pdo->prepare("SELECT s.*, g.*, e.* FROM students s
    LEFT JOIN guardians g ON s.id = g.student_id
    LEFT JOIN enrollments e ON s.id = e.student_id
    WHERE s.id = ?");
  $stmt->execute([$id]);
  $current = $stmt->fetch(PDO::FETCH_ASSOC);

  $noChanges = (
    $current['lrn'] === $lrn &&
    $current['first_name'] === $first_name &&
    $current['middle_name'] === $middle_name &&
    $current['last_name'] === $last_name &&
    $current['dob'] === $dob &&
    $current['gender'] === $gender &&
    $current['nationality'] === $nationality &&
    $current['address'] === $address &&
    $current['barangay'] === $barangay &&
    $current['contact_number'] === $contact_number &&
    $current['name'] === $guardian_name &&
    $current['relationship'] === $relationship &&
    $current['contact_number'] === $guardian_contact &&
    $current['email'] === $guardian_email &&
    $current['occupation'] === $occupation &&
    $current['employer'] === $employer &&
    $current['is_emergency_contact'] == $is_emergency_contact &&
    $current['school_year_id'] == $school_year_id &&
    $current['grade_section_id'] == $grade_section_id &&
    $current['enrollment_date'] === $enrollment_date &&
    $current['previous_school'] === $previous_school &&
    !$photo_path // no new photo
  );

  if ($noChanges) {
    setFlash('info', 'No changes detected. Student record remains the same.');
    header("Location: /pages/admin/student.php?id=$id");
    exit;
  }

  // ✅ Update students
  $stmt = $pdo->prepare("UPDATE students SET
    lrn = :lrn,
    first_name = :first_name,
    middle_name = :middle_name,
    last_name = :last_name,
    dob = :dob,
    gender = :gender,
    nationality = :nationality,
    address = :address,
    barangay = :barangay,
    contact_number = :contact_number" .
    ($photo_path ? ", photo_path = :photo_path" : "") .
    " WHERE id = :id");

  $params = [
    ':lrn' => $lrn,
    ':first_name' => $first_name,
    ':middle_name' => $middle_name ?: null,
    ':last_name' => $last_name,
    ':dob' => $dob,
    ':gender' => $gender,
    ':nationality' => $nationality ?: null,
    ':address' => $address ?: null,
    ':barangay' => $barangay ?: null,
    ':contact_number' => $contact_number ?: null,
    ':id' => $id
  ];
  if ($photo_path) $params[':photo_path'] = $photo_path;

  $stmt->execute($params);

  // ✅ Update guardians
  $stmt = $pdo->prepare("UPDATE guardians SET
    name = :name,
    relationship = :relationship,
    contact_number = :contact_number,
    email = :email,
    occupation = :occupation,
    employer = :employer,
    is_emergency_contact = :is_emergency_contact
    WHERE student_id = :student_id");

  $stmt->execute([
    ':name' => $guardian_name,
    ':relationship' => $relationship,
    ':contact_number' => $guardian_contact ?: null,
    ':email' => $guardian_email ?: null,
    ':occupation' => $occupation ?: null,
    ':employer' => $employer ?: null,
    ':is_emergency_contact' => $is_emergency_contact,
    ':student_id' => $id
  ]);

  // ✅ Update enrollments
  $stmt = $pdo->prepare("UPDATE enrollments SET
    school_year_id = :school_year_id,
    grade_section_id = :grade_section_id,
    enrollment_date = :enrollment_date,
    previous_school = :previous_school
    WHERE student_id = :student_id");

  $stmt->execute([
    ':school_year_id' => $school_year_id,
    ':grade_section_id' => $grade_section_id,
    ':enrollment_date' => $enrollment_date,
    ':previous_school' => $previous_school ?: null,
    ':student_id' => $id
  ]);

  // 🎉 Success
  setFlash('success', 'Student updated successfully.');
  header('Location: /pages/admin/student.php');
  exit;

} catch (Exception $e) {
  setFlash('error', 'Unexpected error: ' . $e->getMessage());
  header("Location: /pages/admin/edit-student.php?id=$id");
  exit;
}