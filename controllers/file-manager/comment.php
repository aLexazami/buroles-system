<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php'; // generateUuid()

$fileId = $_POST['file_id'] ?? null;
$content = trim($_POST['comment'] ?? '');
$userId = $_SESSION['user_id'] ?? null;

// 🔐 Validate session
if (!$userId || !isValidUuid($fileId) || !$content) {
  header('Location: /pages/staff/file-manager.php?error=invalid');
  exit;
}

// 🧠 Insert comment
$stmt = $pdo->prepare("INSERT INTO comments (id, file_id, user_id, content) VALUES (?, ?, ?, ?)");
$stmt->execute([generateUuid(), $fileId, $userId, $content]);

// ✅ Redirect with success
header('Location: /pages/staff/file-manager.php?success=commented');
exit;