<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php';         // isValidUuid()
require_once __DIR__ . '/../../helpers/file-utils.php';   // getFilesForView(), getTrashedRootFolders(), getTrashedChildren()
require_once __DIR__ . '/../../helpers/sharing-utils.php'; // getSharedFolderContents()

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
// 🧼 Trash view logic
if ($view === 'trash') {
  $viewerId = $userId; // may be owner or deleter

  if ($folderId) {
    $check = $pdo->prepare("SELECT is_deleted FROM files WHERE id = ? AND (owner_id = ? OR deleted_by_user_id = ?)");
    $check->execute([$folderId, $viewerId, $viewerId]);
    $folderIsDeleted = (int) $check->fetchColumn() === 1;

    if ($folderIsDeleted) {
      echo json_encode([
        'items' => [],
        'folder_is_deleted' => true
      ]);
      exit;
    }

    $files = getFilesForView($pdo, $viewerId, $view, $folderId, $sortBy, $sortDir);
    echo json_encode([
      'items' => $files,
      'folder_is_deleted' => false
    ]);
    exit;
  }

  $standaloneTrash = getFilesForView($pdo, $viewerId, 'trash', null, $sortBy, $sortDir);
  foreach ($standaloneTrash as &$item) {
    $item['restored_to_fallback'] = ($item['original_path'] && strpos($item['path'], $item['id']) !== false);

    if (!empty($item['original_path'])) {
      $segments = explode('/', $item['original_path']);
      $parentId = count($segments) >= 2 ? $segments[count($segments) - 2] : null;

      if ($parentId) {
        $stmt = $pdo->prepare("SELECT name FROM files WHERE id = ?");
        $stmt->execute([$parentId]);
        $parentName = $stmt->fetchColumn();
        $item['original_parent_name'] = $parentName ?: null;
      }
    }

    if ($item['type'] === 'folder') {
      $item['depth'] = 0;
      $item['children'] = getTrashedChildren($pdo, $item['id'], $viewerId, 1);
    }
  }

  $trashedRoots = getTrashedRootFolders($pdo, $viewerId);
  foreach ($trashedRoots as &$folder) {
    $folder['depth'] = 0;
    if ($folder['type'] === 'folder') {
      $folder['children'] = getTrashedChildren($pdo, $folder['id'], $viewerId, 1);
    }
  }

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

// 📁 Shared-with-me folder context — ✅ prevent root folder from appearing inside itself
if ($view === 'shared-with-me' && $folderId && isValidUuid($folderId)) {
  $files = getSharedFolderContents($pdo, $folderId, $userId, false); // ⛔ exclude root
  echo json_encode([
    'items' => $files,
    'folder_is_deleted' => false
  ]);
  exit;
}

// 📁 Shared-by-me folder context — ✅ FIXED: show recursive contents of shared folder
if ($view === 'shared-by-me' && $folderId && isValidUuid($folderId)) {
  $files = getSharedFolderContents($pdo, $folderId, $userId, false); // ⛔ exclude root
  echo json_encode([
    'items' => $files,
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