<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php';  // generateUuid()
require_once __DIR__ . '/../../helpers/flash.php'; // setFlash()

define('STAFF_ROLE_ID', 1);

$isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
       || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

$input = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;

// 🧭 Preserve view context from query string
$view = $_GET['view'] ?? 'my-files';
$redirectBase = "/pages/staff/file-manager.php?view=" . urlencode($view);

function respond($success, $message, $redirectSuffix = '') {
  global $isJson, $redirectBase;
  if ($isJson) {
    echo json_encode(['success' => $success, 'message' => $message]);
  } else {
    setFlash($success ? 'success' : 'error', $message);
    header("Location: " . $redirectBase . $redirectSuffix);
  }
  exit;
}

// 🔐 Validate session
$grantedBy = $_SESSION['user_id'] ?? null;
if (!is_numeric($grantedBy)) {
  respond(false, 'Invalid session', '&error=session');
}

// 🔐 Validate input
$fileId = $input['file_id'] ?? null;
$email = trim($input['recipient_email'] ?? '');
$permission = $input['permission'] ?? null;

if (!isValidUuid($fileId) || !$email || !$permission) {
  respond(false, 'Invalid input', '&error=invalid');
}

// 🔍 Lookup recipient and enforce staff-only
$stmt = $pdo->prepare("
  SELECT id, role_id
  FROM users
  WHERE email = ? AND is_archived = 0
");
$stmt->execute([$email]);
$recipient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipient || $recipient['role_id'] !== STAFF_ROLE_ID) {
  respond(false, 'Only staff-to-staff sharing is allowed', '&error=invalid-recipient');
}

$recipientId = $recipient['id'];

// 🧠 Prevent duplicate share
$stmt = $pdo->prepare("
  SELECT COUNT(*) FROM access_control
  WHERE file_id = ? AND user_id = ? AND is_revoked = 0
");
$stmt->execute([$fileId, $recipientId]);
if ($stmt->fetchColumn() > 0) {
  respond(false, 'Already shared with this user', '&error=already-shared');
}

// ✅ Insert access control entry
$stmt = $pdo->prepare("
  INSERT INTO access_control (id, file_id, user_id, permission, inherited, granted_by)
  VALUES (?, ?, ?, ?, FALSE, ?)
");
$stmt->execute([generateUuid(), $fileId, $recipientId, $permission, $grantedBy]);

respond(true, 'File shared successfully', '&success=shared');