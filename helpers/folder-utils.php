<?php
require_once __DIR__ . '/path.php'; // ensureVirtualPathExists(), resolveDiskPath(), deleteDirectory()
/***********************************************************************************************************************/
function deleteDiskFilesByFolderId(PDO $pdo, int $userId, string $folderId): void {
  $stmt = $pdo->prepare("SELECT path FROM files WHERE parent_id = ? AND owner_id = ? AND type = 'file'");
  $stmt->execute([$folderId, $userId]);
  $paths = $stmt->fetchAll(PDO::FETCH_COLUMN);

  foreach ($paths as $dbPath) {
    $realPath = __DIR__ . "/../../" . ltrim($dbPath, '/');
    if (is_file($realPath)) {
      unlink($realPath);
    }
  }
}

function deleteFolderAndContents(PDO $pdo, int $userId, string $folderId, bool $isRootCall = true): bool {
  try {
    if ($isRootCall) {
      error_log("🟢 BEGIN transaction for folder $folderId");
      $pdo->beginTransaction();
    }

    // Confirm folder exists
    $stmt = $pdo->prepare("SELECT id, name, path FROM files WHERE id = ? AND owner_id = ? AND type = 'folder'");
    $stmt->execute([$folderId, $userId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$folder) {
      error_log("❌ Folder not found: $folderId");
      throw new Exception("Folder not found");
    }

    error_log("📁 Deleting contents of folder: {$folder['name']} ($folderId)");

    // Delete files (skip standalone-deleted ones)
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
      $realPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
      if (is_file($realPath)) unlink($realPath);

      $log = $pdo->prepare("
        INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
        VALUES (UUID(), ?, ?, ?, 'delete-permanent', ?, 'dashboard')
      ");
      $log->execute([$file['id'], $file['name'], $userId, 'File permanently deleted']);

      $del = $pdo->prepare("DELETE FROM files WHERE id = ?");
      $del->execute([$file['id']]);
    }

    // Delete subfolders (skip standalone-deleted ones)
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
      if (!$success) {
        throw new Exception("Failed to delete subfolder: " . $subfolder['id']);
      }
    }

    // Log and delete folder
    $log = $pdo->prepare("
      INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
      VALUES (UUID(), ?, ?, ?, 'delete-permanent', ?, 'dashboard')
    ");
    $log->execute([$folderId, $folder['name'], $userId, 'Folder permanently deleted']);

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

function softDeleteFolderAndContents(PDO $pdo, int $userId, string $folderId, bool $inherited = false, ?string $trashBasePath = null): bool {
  try {
    $stmt = $pdo->prepare("SELECT path, name FROM files WHERE id = ?");
    $stmt->execute([$folderId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$folder) return false;

    $originalPath = rtrim($folder['path'], '/');
    $originalDiskPath = resolveDiskPath($originalPath);
    $deletedByParent = $inherited ? 1 : 0;

    // ✅ Always compute this folder's trash path
    $trashPath = $trashBasePath ?? "/srv/burol-storage/$userId/trash/$folderId";
    $trashFullPath = resolveDiskPath($trashPath);

    // ✅ Ensure this folder's trash path exists — even if empty
    if (!is_dir($trashFullPath)) {
      mkdir($trashFullPath, 0775, true);
    }

    // ✅ Update this folder in DB
    $update = $pdo->prepare("
      UPDATE files
      SET is_deleted = 1,
          original_path = path,
          path = ?,
          deleted_by_parent = ?,
          updated_at = NOW()
      WHERE id = ?
    ");
    $update->execute([$trashPath, $deletedByParent, $folderId]);

    // 📝 Log folder deletion
    $log = $pdo->prepare("
      INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
      VALUES (UUID(), ?, ?, ?, 'delete', ?, 'dashboard')
    ");
    $log->execute([
      $folderId,
      $folder['name'],
      $userId,
      $inherited ? 'Folder soft-deleted as inherited deletion' : 'Folder soft-deleted recursively'
    ]);

    // 📦 Soft delete all files directly inside this folder
    $stmt = $pdo->prepare("SELECT id, name, path FROM files WHERE parent_id = ? AND type = 'file'");
    $stmt->execute([$folderId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($files as $file) {
      $stmtCheck = $pdo->prepare("SELECT is_deleted, deleted_by_parent FROM files WHERE id = ?");
      $stmtCheck->execute([$file['id']]);
      $meta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

      if ($meta && $meta['is_deleted'] && $meta['deleted_by_parent'] == 0) {
        continue;
      }

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
            updated_at = NOW()
        WHERE id = ?
      ");
      $update->execute([$trashFilePath, $file['id']]);

      $log = $pdo->prepare("
        INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
        VALUES (UUID(), ?, ?, ?, 'delete', ?, 'dashboard')
      ");
      $log->execute([
        $file['id'],
        $file['name'],
        $userId,
        'File soft-deleted as part of folder deletion'
      ]);
    }

    // 🔁 Recursively soft delete subfolders
    $stmt = $pdo->prepare("SELECT id FROM files WHERE parent_id = ? AND type = 'folder'");
    $stmt->execute([$folderId]);
    $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subfolders as $subfolder) {
      $stmtCheck = $pdo->prepare("SELECT is_deleted, deleted_by_parent FROM files WHERE id = ?");
      $stmtCheck->execute([$subfolder['id']]);
      $meta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

      if ($meta && $meta['is_deleted'] && $meta['deleted_by_parent'] == 0) {
        continue;
      }

      // ✅ Compute subfolder's trash path and recurse
      $subfolderTrashPath = rtrim($trashPath, '/') . '/' . $subfolder['id'];
      softDeleteFolderAndContents($pdo, $userId, $subfolder['id'], true, $subfolderTrashPath);
    }

    // ✅ Remove original folder from disk — even if not empty
    deleteDirectory($originalDiskPath);

    return true;
  } catch (Exception $e) {
    error_log("Soft folder deletion failed: " . $e->getMessage());
    return false;
  }
}

function isValidFolderName(string $name): bool {
  return !preg_match('/[\\\\\\/:\*\?"<>|]/', $name);
}

function restoreFolderAndContents(PDO $pdo, int $userId, string $folderId): void {
  $stmt = $pdo->prepare("SELECT path, original_path FROM files WHERE id = ?");
  $stmt->execute([$folderId]);
  $folder = $stmt->fetch(PDO::FETCH_ASSOC);

  $restorePath = $folder['original_path'] ?? $folder['path'];
  ensureVirtualPathExists($restorePath);

  $stmt = $pdo->prepare("
    UPDATE files
    SET is_deleted = 0,
        path = original_path,
        original_path = NULL,
        deleted_by_parent = 0,
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
      restoreFile($pdo, $userId, $child['id']);
    }
  }

  // 🧠 Restore orphaned fallback items only if their parent is restored or null
  $stmt = $pdo->prepare("
    SELECT f.id, f.type
    FROM files f
    LEFT JOIN files p ON f.parent_id = p.id
    WHERE f.is_deleted = 1
      AND f.deleted_by_parent = 0
      AND f.original_path LIKE CONCAT(?, '/%')
      AND (
        f.parent_id IS NULL
        OR (p.is_deleted = 0 AND p.original_path IS NOT NULL AND f.original_path LIKE CONCAT(p.original_path, '/%'))
      )
  ");
  $stmt->execute([$restorePath]);
  $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($orphans as $orphan) {
    if ($orphan['type'] === 'folder') {
      restoreFolderAndContents($pdo, $userId, $orphan['id']);
    } else {
      restoreFile($pdo, $userId, $orphan['id']);
    }
  }
}

function logRestore(PDO $pdo, int $userId, string $fileId, string $details): void {
  $stmt = $pdo->prepare("
    INSERT INTO logs (id, file_id, user_id, action, details, source)
    VALUES (UUID(), ?, ?, 'restore', ?, 'dashboard')
  ");
  $stmt->execute([$fileId, $userId, $details]);
}
?>