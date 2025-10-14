<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo "Missing folder ID.";
  exit;
}

$folderId = $_GET['id'];
$currentUserId = $_SESSION['user_id'] ?? null;

if (!$currentUserId) {
  http_response_code(401);
  echo "Unauthorized.";
  exit;
}

// 📁 Fetch folder metadata
$stmt = $pdo->prepare("SELECT name, is_deleted FROM files WHERE id = ? AND type = 'folder' LIMIT 1");
$stmt->execute([$folderId]);
$folder = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$folder || $folder['is_deleted']) {
  http_response_code(404);
  echo "Folder not found.";
  exit;
}

// 🧠 Safe filename
$safeName = str_replace(["\r", "\n"], '', $folder['name'] ?? 'download');
$zipName = $safeName . '.zip';
$tempZipPath = tempnam(sys_get_temp_dir(), 'folder_') . '.zip';

$zip = new ZipArchive();
if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
  http_response_code(500);
  echo "Failed to create ZIP archive.";
  exit;
}

// 🧠 Recursive bundling
function addFolderToZip($pdo, $zip, $folderId, $relativePath = '') {
  $stmt = $pdo->prepare("SELECT id, name, path, type FROM files WHERE parent_id = ? AND is_deleted = 0");
  $stmt->execute([$folderId]);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($items)) {
    $zip->addEmptyDir($relativePath);
    return;
  }

  foreach ($items as $item) {
    $itemPath = $relativePath . '/' . $item['name'];

    if ($item['type'] === 'folder') {
      $zip->addEmptyDir($itemPath);
      addFolderToZip($pdo, $zip, $item['id'], $itemPath);
    } else {
      $diskPath = __DIR__ . '/../../srv/burol-storage/' . ltrim(str_replace('/srv/burol-storage/', '', $item['path']), '/');
      if (file_exists($diskPath)) {
        $zip->addFile($diskPath, $itemPath);
      }
    }
  }
}

addFolderToZip($pdo, $zip, $folderId, $folder['name']);
$zip->close();

// 🧾 Optional debug log
error_log("Folder download: folder_id=$folderId, user_id=$currentUserId, filename={$folder['name']}.zip");

// 📤 Serve ZIP file
header('Content-Type: application/zip');
header('Content-Length: ' . filesize($tempZipPath));
header('Content-Disposition: attachment; filename="' . $zipName . '"');
readfile($tempZipPath);
unlink($tempZipPath);
exit;