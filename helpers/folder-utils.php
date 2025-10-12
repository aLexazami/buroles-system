<?php
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

function deleteFolderAndContents(PDO $pdo, int $userId, string $folderId): bool {
  try {
    $pdo->beginTransaction();

    // Step 1: Delete all files in this folder (from disk + DB)
    $stmt = $pdo->prepare("SELECT id, name, path FROM files WHERE parent_id = ? AND owner_id = ? AND type = 'file'");
    $stmt->execute([$folderId, $userId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($files as $file) {
      $realPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
      if (is_file($realPath)) unlink($realPath);

      // 📝 Log file deletion BEFORE DB removal
      $log = $pdo->prepare("
        INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
        VALUES (UUID(), ?, ?, ?, 'delete-permanent', ?, 'dashboard')
      ");
      $log->execute([
        $file['id'],
        $file['name'],
        $userId,
        'File permanently deleted'
      ]);

      // 🗑️ Delete file from DB
      $del = $pdo->prepare("DELETE FROM files WHERE id = ?");
      $del->execute([$file['id']]);
    }

    // Step 2: Recursively delete subfolders
    $stmt = $pdo->prepare("SELECT id, name FROM files WHERE parent_id = ? AND owner_id = ? AND type = 'folder'");
    $stmt->execute([$folderId, $userId]);
    $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subfolders as $subfolder) {
      deleteFolderAndContents($pdo, $userId, $subfolder['id']); // recursion
    }

    // Step 3: Fetch folder name for logging
    $stmt = $pdo->prepare("SELECT name FROM files WHERE id = ? AND owner_id = ? AND type = 'folder'");
    $stmt->execute([$folderId, $userId]);
    $folderName = $stmt->fetchColumn() ?? 'Folder';

    // 📝 Log folder deletion BEFORE DB removal
    $log = $pdo->prepare("
      INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
      VALUES (UUID(), ?, ?, ?, 'delete-permanent', ?, 'dashboard')
    ");
    $log->execute([
      $folderId,
      $folderName,
      $userId,
      'Folder permanently deleted'
    ]);

    // 🗑️ Delete folder from DB
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND owner_id = ? AND type = 'folder'");
    $stmt->execute([$folderId, $userId]);

    $pdo->commit();
    return true;

  } catch (Exception $e) {
    $pdo->rollBack();
    error_log("Folder deletion failed: " . $e->getMessage());
    return false;
  }
}

function softDeleteFolderAndContents(PDO $pdo, int $userId, string $folderId, bool $inherited = false): bool {
  try {
    $stmt = $pdo->prepare("SELECT path, name FROM files WHERE id = ?");
    $stmt->execute([$folderId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$folder) return false;

    $originalPath = $folder['path'];
    $trashPath = "/srv/burol-storage/$userId/trash/$folderId";
    $deletedByParent = $inherited ? 1 : 0;

    // ✅ Soft delete this folder
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
        continue; // ✅ Skip standalone-deleted files
      }

      $realPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
      $trashFilePath = "/srv/burol-storage/$userId/trash/" . basename($realPath);
      $trashFullPath = __DIR__ . "/../../" . ltrim($trashFilePath, '/');

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
    $stmt = $pdo->prepare("SELECT id, name FROM files WHERE parent_id = ? AND type = 'folder'");
    $stmt->execute([$folderId]);
    $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subfolders as $subfolder) {
      // 🧠 Check if already deleted independently
      $stmtCheck = $pdo->prepare("SELECT is_deleted, deleted_by_parent FROM files WHERE id = ?");
      $stmtCheck->execute([$subfolder['id']]);
      $meta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

      if ($meta && $meta['is_deleted'] && $meta['deleted_by_parent'] == 0) {
        continue; // ✅ Skip standalone-deleted folders
      }

      softDeleteFolderAndContents($pdo, $userId, $subfolder['id'], true); // ✅ inherited
    }

    return true;
  } catch (Exception $e) {
    error_log("Soft folder deletion failed: " . $e->getMessage());
    return false;
  }
}

function isValidFolderName(string $name): bool {
  return !preg_match('/[\\\\\\/:\*\?"<>|]/', $name);
}

?>