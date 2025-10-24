<?php
require_once __DIR__ . '/path.php'; // ensureVirtualPathExists(), resolveDiskPath(), deleteDirectory()
/***********************************************************************************************************************/

/**
 * Permanently delete a folder from disk using its virtual path.
 * Automatically resolves the physical path and removes all contents.
 */
function deleteFolderOnDisk(string $virtualPath): void
{
  $diskPath = resolveDiskPath($virtualPath);
  deleteDirectory($diskPath);
}

function getRecursiveFolderSize(PDO $pdo, string $folderId): int
{
  $totalSize = 0;

  // Count all direct files (regardless of owner)
  $stmt = $pdo->prepare("
    SELECT size FROM files
    WHERE parent_id = ? AND type = 'file' AND is_deleted = 0
  ");
  $stmt->execute([$folderId]);
  $sizes = $stmt->fetchAll(PDO::FETCH_COLUMN);
  foreach ($sizes as $size) {
    $totalSize += (int) $size;
  }

  // Recurse into subfolders (regardless of owner)
  $stmt = $pdo->prepare("
    SELECT id FROM files
    WHERE parent_id = ? AND type = 'folder' AND is_deleted = 0
  ");
  $stmt->execute([$folderId]);
  $subfolders = $stmt->fetchAll(PDO::FETCH_COLUMN);
  foreach ($subfolders as $subId) {
    $totalSize += getRecursiveFolderSize($pdo, $subId);
  }

  return $totalSize;
}

function getFolderMetadata(PDO $pdo, string $folderId, int $userId): ?array
{
  $stmt = $pdo->prepare("SELECT id, name, path FROM files WHERE id = ? AND owner_id = ? AND type = 'folder'");
  $stmt->execute([$folderId, $userId]);
  $folder = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$folder) error_log("❌ Folder not found: $folderId");
  return $folder ?: null;
}

function deleteChildFiles(PDO $pdo, int $userId, string $folderId): void
{
  $stmt = $pdo->prepare("
    SELECT id, name, path, is_deleted, deleted_by_parent
    FROM files
    WHERE parent_id = ? AND owner_id = ? AND type = 'file'
  ");
  $stmt->execute([$folderId, $userId]);
  $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($files as $file) {
    if ($file['is_deleted'] && $file['deleted_by_parent'] == 0) {
      error_log("⏭️ Skipping standalone-deleted file: {$file['name']} ({$file['id']})");
      continue;
    }

    error_log("🗑️ Deleting file: {$file['name']} ({$file['id']})");
    $realPath = resolveDiskPath($file['path']);
    if (is_file($realPath)) unlink($realPath);

    logDeletion($pdo, $file['id'], $file['name'], $userId, 'File permanently deleted');

    $del = $pdo->prepare("DELETE FROM files WHERE id = ?");
    $del->execute([$file['id']]);
  }
}

function deleteChildFolders(PDO $pdo, int $userId, string $folderId): void
{
  $stmt = $pdo->prepare("
    SELECT id, is_deleted, deleted_by_parent
    FROM files
    WHERE parent_id = ? AND owner_id = ? AND type = 'folder'
  ");
  $stmt->execute([$folderId, $userId]);
  $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($subfolders as $subfolder) {
    if ($subfolder['is_deleted'] && $subfolder['deleted_by_parent'] == 0) {
      error_log("⏭️ Skipping standalone-deleted folder: {$subfolder['id']}");
      continue;
    }

    error_log("🔁 Recursing into subfolder: {$subfolder['id']}");
    $success = deleteFolderAndContents($pdo, $userId, $subfolder['id'], false);
    if (!$success) throw new Exception("Failed to delete subfolder: " . $subfolder['id']);
  }
}

function logDeletion(PDO $pdo, string $fileId, string $fileName, int $userId, string $details): void
{
  $log = $pdo->prepare("
    INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
    VALUES (UUID(), ?, ?, ?, 'delete-permanent', ?, 'dashboard')
  ");
  $log->execute([$fileId, $fileName, $userId, $details]);
}

function deleteFolderAndContents(PDO $pdo, int $userId, string $folderId, bool $isRootCall = true): bool
{
  try {
    if ($isRootCall) {
      error_log("🟢 BEGIN transaction for folder $folderId");
      $pdo->beginTransaction();
    }

    $folder = getFolderMetadata($pdo, $folderId, $userId);
    if (!$folder) throw new Exception("Folder not found");

    error_log("📁 Deleting contents of folder: {$folder['name']} ($folderId)");

    deleteChildFiles($pdo, $userId, $folderId);
    deleteChildFolders($pdo, $userId, $folderId);

    logDeletion($pdo, $folderId, $folder['name'], $userId, 'Folder permanently deleted');
    deleteFolderOnDisk($folder['path']);

    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND owner_id = ? AND type = 'folder'");
    $stmt->execute([$folderId, $userId]);

    if ($isRootCall) {
      $pdo->commit();
      error_log("✅ COMMIT transaction for folder $folderId");
    }

    return true;
  } catch (Exception $e) {
    if ($isRootCall && $pdo->inTransaction()) {
      $pdo->rollBack();
      error_log("🔴 ROLLBACK transaction for folder $folderId");
    }
    error_log("❌ Folder deletion failed: " . $e->getMessage());
    return false;
  }
}

function softDeleteFolderAndContents(
  PDO $pdo,
  int $actorId,              // 👤 Who performed the deletion
  string $folderId,
  bool $inherited = false,
  ?string $trashBasePath = null
): bool {
  try {
    $stmt = $pdo->prepare("SELECT path, name FROM files WHERE id = ?");
    $stmt->execute([$folderId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$folder) return false;

    $originalPath = rtrim($folder['path'], '/');
    $originalDiskPath = resolveDiskPath($originalPath);
    $deletedByParent = $inherited ? 1 : 0;

    $trashPath = $trashBasePath ?? "/srv/burol-storage/$actorId/trash/$folderId";
    $trashFullPath = resolveDiskPath($trashPath);

    if (!is_dir($trashFullPath)) {
      mkdir($trashFullPath, 0775, true);
    }

    // ✅ Soft-delete folder itself
    $update = $pdo->prepare("
      UPDATE files
      SET is_deleted = 1,
          original_path = path,
          path = ?,
          deleted_by_parent = ?,
          deleted_by_user_id = ?,
          updated_at = NOW()
      WHERE id = ?
    ");
    $update->execute([$trashPath, $deletedByParent, $actorId, $folderId]);

    $log = $pdo->prepare("
      INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
      VALUES (UUID(), ?, ?, ?, 'delete', ?, 'dashboard')
    ");
    $log->execute([
      $folderId,
      $folder['name'],
      $actorId,
      $inherited ? 'Folder soft-deleted as inherited deletion' : 'Folder soft-deleted recursively'
    ]);

    // 📦 Soft-delete files inside folder
    $stmt = $pdo->prepare("SELECT id, name, path FROM files WHERE parent_id = ? AND type = 'file'");
    $stmt->execute([$folderId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($files as $file) {
      $stmtCheck = $pdo->prepare("SELECT is_deleted, deleted_by_parent FROM files WHERE id = ?");
      $stmtCheck->execute([$file['id']]);
      $meta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

      if ($meta && $meta['is_deleted'] && $meta['deleted_by_parent'] == 0) continue;

      $realPath = resolveDiskPath($file['path']);
      $relativePath = substr($file['path'], strlen($originalPath));
      $trashFilePath = rtrim($trashPath, '/') . $relativePath;
      $trashFullPath = resolveDiskPath($trashFilePath);

      ensureVirtualPathExists($trashFilePath);

      if (is_file($realPath)) {
        rename($realPath, $trashFullPath);
      }

      $update = $pdo->prepare("
        UPDATE files
        SET is_deleted = 1,
            original_path = path,
            path = ?,
            deleted_by_parent = 1,
            deleted_by_user_id = ?,
            updated_at = NOW()
        WHERE id = ?
      ");
      $update->execute([$trashFilePath, $actorId, $file['id']]);

      $log = $pdo->prepare("
        INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
        VALUES (UUID(), ?, ?, ?, 'delete', ?, 'dashboard')
      ");
      $log->execute([
        $file['id'],
        $file['name'],
        $actorId,
        'File soft-deleted as part of folder deletion'
      ]);
    }

    // 🔁 Recursively soft-delete subfolders
    $stmt = $pdo->prepare("SELECT id FROM files WHERE parent_id = ? AND type = 'folder'");
    $stmt->execute([$folderId]);
    $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subfolders as $subfolder) {
      $stmtCheck = $pdo->prepare("SELECT is_deleted, deleted_by_parent FROM files WHERE id = ?");
      $stmtCheck->execute([$subfolder['id']]);
      $meta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

      if ($meta && $meta['is_deleted'] && $meta['deleted_by_parent'] == 0) continue;

      $subfolderTrashPath = rtrim($trashPath, '/') . '/' . $subfolder['id'];
      softDeleteFolderAndContents($pdo, $actorId, $subfolder['id'], true, $subfolderTrashPath);
    }

    deleteDirectory($originalDiskPath);
    return true;
  } catch (Exception $e) {
    error_log("Soft folder deletion failed: " . $e->getMessage());
    return false;
  }
}

function isValidFolderName(string $name): bool
{
  return !preg_match('/[\\\\\\/:\*\?"<>|]/', $name);
}

function restoreFolderAndContents(PDO $pdo, int $userId, string $folderId): void
{
  // 🧠 Fetch folder metadata
  $stmt = $pdo->prepare("SELECT id, path, original_path, owner_id FROM files WHERE id = ?");
  $stmt->execute([$folderId]);
  $folder = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$folder) {
    throw new Exception("Restore failed: folder not found.");
  }

  $restorePath = $folder['original_path'] ?? $folder['path'];
  $restoreFullPath = __DIR__ . "/../../" . ltrim($restorePath, '/');
  $trashPath = __DIR__ . "/../../" . ltrim($folder['path'], '/');

  // 🧠 Ensure parent hierarchy exists
  ensureFolderHierarchyExists($pdo, $folderId);

  // ✅ Move folder from trash if needed
  if (is_dir($trashPath)) {
    $restoreExists = is_dir($restoreFullPath);
    $restoreEmpty = $restoreExists && count(scandir($restoreFullPath)) <= 2;

    if (!$restoreExists || $restoreEmpty) {
      if (!is_writable(dirname($restoreFullPath))) {
        throw new Exception("Restore failed: destination folder is not writable.");
      }

      if ($restoreExists && !$restoreEmpty) {
        throw new Exception("Restore failed: destination folder already exists and is not empty.");
      }

      if (!rename($trashPath, $restoreFullPath)) {
        throw new Exception("Restore failed: unable to move folder from trash.");
      }
    }
  }

  // ✅ Update metadata
  $stmt = $pdo->prepare("
  UPDATE files
  SET is_deleted = 0,
      path = original_path,
      original_path = NULL,
      deleted_by_parent = 0,
      deleted_by_user_id = NULL,
      updated_at = NOW()
  WHERE id = ?
");
  $stmt->execute([$folderId]);

  logRestore($pdo, $userId, $folderId, 'Folder restored from trash');

  // 🔁 Restore children with deleted_by_parent = 1
  $stmt = $pdo->prepare("
    SELECT id, type FROM files
    WHERE parent_id = ? AND is_deleted = 1 AND deleted_by_parent = 1
  ");
  $stmt->execute([$folderId]);
  $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($children as $child) {
    if ($child['type'] === 'folder') {
      restoreFolderAndContents($pdo, $userId, $child['id']);
    } else {
      $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
      $stmt->execute([$child['id']]);
      $childFile = $stmt->fetch(PDO::FETCH_ASSOC);
      restoreFile($pdo, $userId, $childFile);
    }
  }

  // 🧠 Restore orphaned fallback items only if their parent is restored or null
  $stmt = $pdo->prepare("
    SELECT f.id, f.type
    FROM files f
    LEFT JOIN files p ON f.parent_id = p.id
    WHERE f.is_deleted = 1
      AND f.deleted_by_parent = 0
      AND f.original_path COLLATE utf8mb4_general_ci LIKE CONCAT(?, '/%')
      AND (
        f.parent_id IS NULL
        OR (
          p.is_deleted = 0
          AND p.original_path IS NOT NULL
          AND f.original_path COLLATE utf8mb4_general_ci LIKE CONCAT(p.original_path, '/%')
        )
      )
  ");
  $stmt->execute([$restorePath]);
  $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($orphans as $orphan) {
    if ($orphan['type'] === 'folder') {
      restoreFolderAndContents($pdo, $userId, $orphan['id']);
    } else {
      $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
      $stmt->execute([$orphan['id']]);
      $orphanFile = $stmt->fetch(PDO::FETCH_ASSOC);
      restoreFile($pdo, $userId, $orphanFile);
    }
  }
}

function ensureFolderHierarchyExists(PDO $pdo, string $folderId): void
{
  $currentId = $folderId;
  $paths = [];

  while ($currentId) {
    $stmt = $pdo->prepare("SELECT id, parent_id, original_path, path FROM files WHERE id = ?");
    $stmt->execute([$currentId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$folder) break;

    $paths[] = $folder['original_path'] ?? $folder['path'];
    $currentId = $folder['parent_id'];
  }

  // Create from top to bottom
  foreach (array_reverse($paths) as $path) {
    ensureVirtualPathExists($path);
  }
}
