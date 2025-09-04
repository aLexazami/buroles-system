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
$folderName = trim($_POST['folder_name'] ?? '');

if (empty($folderName)) {
    setFlash('error', 'Folder name is required.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $folderName)) {
    setFlash('error', 'Invalid folder name. Use only letters, numbers, dashes, and spaces.');
    header("Location: /pages/staff/file-manager.php");
    exit();
}

$basePath = __DIR__ . "/../uploads/staff/$userId/";
$fullPath = $basePath . $folderName;

if (!file_exists($fullPath)) {
    if (mkdir($fullPath, 0755, true)) {
        setFlash('success', "Folder '$folderName' created successfully.");
    } else {
        setFlash('error', 'Failed to create folder. Check server permissions.');
    }
} else {
    setFlash('warning', "Folder '$folderName' already exists.");
}

header("Location: /pages/staff/file-manager.php");
exit();