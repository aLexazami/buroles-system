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

// 📁 Fetch root folder
$stmt = $pdo->prepare("SELECT name, owner_id, is_deleted FROM files WHERE id = ? AND type = 'folder' LIMIT 1");
$stmt->execute([$folderId]);
$folder = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$folder || $folder['is_deleted']) {
  http_response_code(404);
  echo "Folder not found.";
  exit;
}

if ($folder['owner_id'] !== $currentUserId) {
  http_response_code(403);
  echo "Forbidden.";
  exit;
}

$zip = new ZipArchive();
$tempZipPath = tempnam(sys_get_temp_dir(), 'folder_') . '.zip';

if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
  http_response_code(500);
  echo "Failed to create ZIP archive.";
  exit;
}

// 🧠 Recursive fetch function
function addFolderToZip($pdo, $zip, $folderId, $relativePath = '') {
  $stmt = $pdo->prepare("SELECT id, name, path, type FROM files WHERE parent_id = ? AND is_deleted = 0");
  $stmt->execute([$folderId]);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($items)) {
    $zip->addEmptyDir($relativePath); // preserve empty folder
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

// 🧩 Start recursive bundling
addFolderToZip($pdo, $zip, $folderId, $folder['name']);
$zip->close();

// 🧾 Log folder download to database
$logStmt = $pdo->prepare("
  INSERT INTO downloads (id, file_id, user_id, view_context, folder_id, ip_address, user_agent)
  VALUES (UUID(), ?, ?, ?, ?, ?, ?)
");
$logStmt->execute([
  $folderId,
  $currentUserId,
  $_GET['view'] ?? null,
  $_GET['folder'] ?? null,
  $_SERVER['REMOTE_ADDR'] ?? null,
  $_SERVER['HTTP_USER_AGENT'] ?? null
]);

// 🧾 Optional: Log to error_log for debugging
error_log("Folder download: folder_id=$folderId, user_id=$currentUserId, filename={$folder['name']}.zip");

// 📤 Serve ZIP file
header('Content-Type: application/zip');
header('Content-Length: ' . filesize($tempZipPath));
header('Content-Disposition: attachment; filename="' . $folder['name'] . '.zip"');
readfile($tempZipPath);

// 🧹 Cleanup
unlink($tempZipPath);
exit;