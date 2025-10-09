<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php'; // generateUuid()

$fileId = $_POST['file_id'] ?? null;
$email = trim($_POST['recipient_email'] ?? '');
$permission = $_POST['permission'] ?? null;
$grantedBy = $_SESSION['user_id'] ?? null;

// 🔐 Validate input
if (!isValidUuid($fileId) || !$email || !$permission || !$grantedBy) {
  header('Location: /pages/staff/file-manager.php?error=invalid');
  exit;
}

// 🔍 Lookup recipient
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$recipient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipient) {
  header('Location: /pages/staff/file-manager.php?error=user-not-found');
  exit;
}

$recipientId = $recipient['id'];

// 🧠 Insert access control entry
$stmt = $pdo->prepare("
  INSERT INTO access_control (id, file_id, user_id, permission, inherited, granted_by)
  VALUES (?, ?, ?, ?, FALSE, ?)
");
$stmt->execute([generateUuid(), $fileId, $recipientId, $permission, $grantedBy]);

// ✅ Redirect with success
header('Location: /pages/staff/file-manager.php?success=shared');
exit;