<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// ✅ Guard: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// ✅ Optional: Double-check must_change_password from DB
$stmt = $pdo->prepare("SELECT must_change_password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$mustChange = $stmt->fetchColumn();

if (!$mustChange) {
  header("Location: ../index.php"); // Or redirect to dashboard
  exit();
}

// ✅ Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  // ✅ Validate input
  if (trim($new) !== trim($confirm)) {
    echo "❌ Passwords do not match.";
    exit();
  }

  if (strlen($new) < 8) {
    echo "❌ Password must be at least 8 characters.";
    exit();
  }

  // ✅ Update password
  $hashed = password_hash($new, PASSWORD_DEFAULT);
  $update = $pdo->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
  $success = $update->execute([$hashed, $_SESSION['user_id']]);


  if ($success) {
    echo "✅ Password updated successfully! Redirecting...";

    // ✅ Safe redirect based on role_id
    $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $roleId = $stmt->fetchColumn();

    switch ($roleId) {
      case 1:
        header("Refresh: 3; URL=/pages/main-staff.php");
        break;
      case 2:
        header("Refresh: 3; URL=/pages/main-admin.php");
        break;
    }

    exit();
  } else {
    echo "❌ Failed to update password. Please try again.";
    exit();
  }
}
?>

<!-- Simple HTML Form -->
<form method="POST" class="space-y-4 max-w-md mx-auto">
  <input type="password" name="new_password" placeholder="New Password" required class="input">
  <input type="password" name="confirm_password" placeholder="Confirm Password" required class="input">
  <button type="submit" class="btn-primary">Update Password</button>
</form>