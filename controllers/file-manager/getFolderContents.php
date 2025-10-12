<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php';         // isValidUuid()
require_once __DIR__ . '/../../helpers/file-utils.php';   // getFilesForView(), getTrashedRootFolders(), getTrashedChildren()

header('Content-Type: application/json');

// 🛡️ Auth check
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// 📥 Input sanitization
$folderId = $_GET['folder_id'] ?? null;
$folderId = isValidUuid($folderId) ? $folderId : null;

$view     = $_GET['view']     ?? 'my-files';
$sortBy   = $_GET['sort_by']  ?? 'updated_at';
$sortDir  = $_GET['sort_dir'] ?? 'DESC';

// 🧼 Trash view logic
if ($view === 'trash') {
  if ($folderId) {
    // 🔒 Check if folder is deleted
    $check = $pdo->prepare("SELECT is_deleted FROM files WHERE id = ? AND owner_id = ?");
    $check->execute([$folderId, $userId]);
    $folderIsDeleted = (int) $check->fetchColumn() === 1;

    if ($folderIsDeleted) {
      echo json_encode([
        'items' => [],
        'folder_is_deleted' => true
      ]);
      exit;
    }

    // ✅ Folder is not deleted — fetch its trashed children
    $files = getFilesForView($pdo, $userId, $view, $folderId, $sortBy, $sortDir);
    echo json_encode([
      'items' => $files,
      'folder_is_deleted' => false
    ]);
    exit;
  }

  // 🧩 Trash root view — show both standalone and nested deletions

  // ✅ Standalone deleted items (deleted_by_parent = 0)
  $standaloneTrash = getFilesForView($pdo, $userId, 'trash', null, $sortBy, $sortDir);

  foreach ($standaloneTrash as &$item) {
    $item['restored_to_fallback'] = ($item['original_path'] && strpos($item['path'], $item['id']) !== false);

    if ($item['type'] === 'folder') {
      $item['depth'] = 0;
      $item['children'] = getTrashedChildren($pdo, $item['id'], $userId, 1);
    }
  }

  // ✅ Root-level folders with nested deletions (deleted_by_parent = 1)
  $trashedRoots = getTrashedRootFolders($pdo, $userId);

  foreach ($trashedRoots as &$folder) {
    $folder['depth'] = 0;
    if ($folder['type'] === 'folder') {
      $folder['children'] = getTrashedChildren($pdo, $folder['id'], $userId, 1);
    }
  }

  // 🧠 Merge both views without duplication
  $merged = array_merge($standaloneTrash, $trashedRoots);
  $seen = [];

  $items = array_filter($merged, function ($item) use (&$seen) {
    if (in_array($item['id'], $seen)) return false;
    $seen[] = $item['id'];
    return true;
  });

  echo json_encode([
    'items' => array_values($items),
    'folder_is_deleted' => false
  ]);
  exit;
}

// 📄 Non-trash views
$files = getFilesForView($pdo, $userId, $view, $folderId, $sortBy, $sortDir);

echo json_encode([
  'items' => $files,
  'folder_is_deleted' => false
]);