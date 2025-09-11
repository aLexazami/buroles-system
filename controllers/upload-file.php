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
  exit;
}

$userId = $_SESSION['user_id'];
$path = trim($_POST['path'] ?? '', '/');

if (preg_match('/\.\.|[<>:"|?*]/', $path)) {
  setFlash('error', 'Invalid folder path.');
  header("Location: /pages/staff/file-manager.php");
  exit;
}

$basePath = __DIR__ . "/../uploads/staff/$userId/";
$uploadDir = $basePath . ($path ? $path . '/' : '');

if (!file_exists($uploadDir)) {
  setFlash('error', 'Target folder does not exist.');
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
  exit;
}

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
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
  exit;
}

$mimeType = mime_content_type($file['tmp_name']);
$filename = basename($file['name']);
$targetPath = $uploadDir . $filename;

if (!array_key_exists($mimeType, $allowedTypes)) {
  setFlash('error', 'Unsupported file type.');
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
  exit;
}

if (file_exists($targetPath)) {
  setFlash('warning', "File '$filename' already exists.");
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
  exit;
}

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
  setFlash('success', "File '$filename' uploaded successfully.");
} else {
  setFlash('error', 'Failed to move uploaded file.');
}

header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
exit;