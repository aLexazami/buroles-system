<?php
session_start();
require_once __DIR__ . '/../auth/session.php';

$userId = $_SESSION['user_id'];
$type = $_POST['type'] ?? '';
$oldName = $_POST['old_name'] ?? '';
$newName = $_POST['new_name'] ?? '';
$path = trim($_POST['path'] ?? '', '/');

$basePath = __DIR__ . "/../uploads/staff/$userId/";
$oldPath = $basePath . ($path ? $path . '/' : '') . $oldName;
$newPath = $basePath . ($path ? $path . '/' : '') . $newName;

if (file_exists($oldPath) && !file_exists($newPath)) {
  rename($oldPath, $newPath);
}

header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
exit;
?>