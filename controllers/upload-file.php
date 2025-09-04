<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';

if (!isset($_SESSION['user_id'])) {
    setFlash('error', 'Unauthorized access.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

$userId = $_SESSION['user_id'];
$targetFolder = trim($_POST['target_folder'] ?? '');

if (empty($targetFolder)) {
    setFlash('error', 'Target folder is required.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

// 🧼 Sanitize folder name
if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $targetFolder)) {
    setFlash('error', 'Invalid folder name.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

$basePath = __DIR__ . "/../uploads/staff/$userId/";
$uploadDir = $basePath . $targetFolder . '/';

if (!file_exists($uploadDir)) {
    setFlash('error', 'Target folder does not exist.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

// ✅ Allowed MIME types and extensions
$allowedTypes = [
    'application/pdf' => 'pdf',
    'text/csv' => 'csv',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'image/jpeg' => 'jpg',
    'image/png' => 'png'
];

$file = $_FILES['file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    setFlash('error', 'File upload failed.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

$mimeType = mime_content_type($file['tmp_name']);
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

if (!array_key_exists($mimeType, $allowedTypes)) {
    setFlash('error', 'Unsupported file type.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

// 🧱 Final path
$filename = basename($file['name']);
$targetPath = $uploadDir . $filename;

// Optional: prevent overwrite
if (file_exists($targetPath)) {
    setFlash('warning', "File '$filename' already exists.");
    header("Location: /pages/staff/file-manager.php");
    exit();
}

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    setFlash('success', "File '$filename' uploaded successfully.");
} else {
    setFlash('error', 'Failed to move uploaded file.');
}

header("Location: /pages/staff/file-manager.php");
exit();