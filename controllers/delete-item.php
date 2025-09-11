<?php
session_start();
require_once __DIR__ . '/../auth/session.php';

$userId = $_SESSION['user_id'];
$type = $_POST['type'] ?? '';
$name = $_POST['name'] ?? '';
$path = trim($_POST['path'] ?? '', '/');

$basePath = __DIR__ . "/../uploads/staff/$userId/";
$targetPath = $basePath . ($path ? $path . '/' : '') . $name;

if ($type === 'file' && is_file($targetPath)) {
  unlink($targetPath);
} elseif ($type === 'folder' && is_dir($targetPath)) {
  rmdir($targetPath); // only works if folder is empty
}

header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
exit;
?>