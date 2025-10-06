<?php

/**
 * Safely scan a directory and return structured metadata for folders and files.
 */
function listFolderItems(string $path): array {
  if (!is_dir($path)) return ['folders' => [], 'files' => []];

  $folders = [];
  $files = [];

  foreach (getDirectoryItems($path) as $item) {
    $fullPath = $path . DIRECTORY_SEPARATOR . $item;

    if (is_dir($fullPath)) {
      $folders[] = buildFolderMetadata($item, $fullPath);
    } elseif (is_file($fullPath)) {
      $files[] = buildFileMetadata($item, $fullPath);
    }
  }

  return ['folders' => $folders, 'files' => $files];
}

/**
 * Recursively scan a folder and return a tree structure.
 */
function listFolderItemsRecursive(string $path): array {
  if (!is_dir($path)) return [];

  $tree = [];

  foreach (getDirectoryItems($path) as $item) {
    $fullPath = $path . DIRECTORY_SEPARATOR . $item;

    if (is_dir($fullPath)) {
      $tree[] = [
        'type' => 'folder',
        'name' => $item,
        'path' => $fullPath,
        'children' => listFolderItemsRecursive($fullPath)
      ];
    } elseif (is_file($fullPath)) {
      $tree[] = [
        'type' => 'file',
        'name' => $item,
        'path' => $fullPath,
        'size' => filesize($fullPath),
        'modified' => formatModifiedTime($fullPath)
      ];
    }
  }

  return $tree;
}

/**
 * Return filtered directory items (excluding . and ..).
 */
function getDirectoryItems(string $path): array {
  return array_filter(scandir($path), fn($item) => $item !== '.' && $item !== '..');
}

/**
 * Build metadata for a folder.
 */
function buildFolderMetadata(string $name, string $fullPath): array {
  $size = getFolderSize($fullPath);
  return [
    'name' => $name,
    'path' => $fullPath,
    'modified' => formatModifiedTime($fullPath),
    'fileCount' => countFilesInFolder($fullPath),
    'size' => $size,
    'readableSize' => formatSize($size)
  ];
}

/**
 * Build metadata for a file.
 */
function buildFileMetadata(string $name, string $fullPath): array {
  $size = filesize($fullPath);
  return [
    'name' => $name,
    'path' => $fullPath,
    'modified' => formatModifiedTime($fullPath),
    'size' => $size,
    'readableSize' => formatSize($size)
  ];
}

/**
 * Get total size of a folder including nested contents.
 */
function getFolderSize(string $folderPath): int {
  $size = 0;
  if (!is_dir($folderPath)) return 0;

  foreach (getDirectoryItems($folderPath) as $item) {
    $fullPath = $folderPath . DIRECTORY_SEPARATOR . $item;

    if (is_file($fullPath)) {
      $size += filesize($fullPath);
    } elseif (is_dir($fullPath)) {
      $size += getFolderSize($fullPath);
    }
  }

  return $size;
}

/**
 * Count only files inside a folder (including nested).
 */
function countFilesInFolder(string $folderPath): int {
  $count = 0;
  if (!is_dir($folderPath)) return 0;

  foreach (getDirectoryItems($folderPath) as $item) {
    $fullPath = $folderPath . DIRECTORY_SEPARATOR . $item;

    if (is_file($fullPath)) {
      $count++;
    } elseif (is_dir($fullPath)) {
      $count += countFilesInFolder($fullPath);
    }
  }

  return $count;
}

/**
 * Get folder stats in one call.
 */
function getFolderStats(string $folderPath): array {
  return [
    'size' => getFolderSize($folderPath),
    'fileCount' => countFilesInFolder($folderPath),
    'modified' => formatModifiedTime($folderPath)
  ];
}

/**
 * Format bytes into human-readable size.
 */
function formatSize(int $bytes): string {
  if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
  if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
  if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
}

/**
 * Format last modified time of a file or folder.
 */
function formatModifiedTime(string $path): string {
  return file_exists($path) ? date("M d, Y H:i", filemtime($path)) : 'Unknown';
}

/**
 * Validate a path before performing destructive operations.
 */
function isSafePath(string $path): bool {
  $realBase = realpath(__DIR__ . '/../uploads');
  $realPath = realpath($path);

  return $realPath !== false && str_starts_with($realPath, $realBase);
}

/**
 * Create a folder safely inside a base path.
 * Supports nested folders like "Reports/2025/September".
 */
function createFolder(string $basePath, string $folderPath): bool {
  $segments = explode('/', trim($folderPath, '/'));
  $safeSegments = array_filter(array_map('sanitizeSegment', $segments));

  if (empty($safeSegments)) return false;

  $target = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $safeSegments);

  if (!file_exists($target)) {
    if (!mkdir($target, 0755, true)) {
      error_log("createFolder: failed to create → $target");
      return false;
    }
  }

  return true;
}

/**
 * Recursively delete a folder and its contents.
 */
function deleteFolderRecursive(string $folderPath): bool {
  if (!isSafePath($folderPath) || !is_dir($folderPath)) return false;

  foreach (getDirectoryItems($folderPath) as $item) {
    $fullItem = $folderPath . DIRECTORY_SEPARATOR . $item;

    if (is_dir($fullItem)) {
      if (!deleteFolderRecursive($fullItem)) return false;
    } else {
      if (!unlink($fullItem)) {
        error_log("deleteFolderRecursive: failed to delete file → $fullItem");
        return false;
      }
    }
  }

  return rmdir($folderPath);
}

/**
 * Recursively move a folder and its contents to a new location.
 * Used as a fallback when rename() fails on non-empty directories.
 */
function moveFolderRecursively(string $source, string $destination): bool {
  if (!isSafePath($source) || !is_dir($source)) return false;

  if (!mkdir($destination, 0755, true)) {
    error_log("moveFolderRecursively: failed to create destination → $destination");
    return false;
  }

  foreach (getDirectoryItems($source) as $item) {
    $src = $source . DIRECTORY_SEPARATOR . $item;
    $dst = $destination . DIRECTORY_SEPARATOR . $item;

    if (is_dir($src)) {
      if (!moveFolderRecursively($src, $dst)) return false;
    } else {
      if (!rename($src, $dst)) {
        error_log("moveFolderRecursively: failed to move file → $src to $dst");
        return false;
      }
    }
  }

  return rmdir($source);
}

/**
 * Recursively fetch all subfolders and files under a given folder ID.
 *
 * @param PDO $pdo
 * @param int $folderId
 * @return array ['folders' => [...], 'files' => [...]]
 */
function getFolderContentsRecursive(PDO $pdo, int $folderId): array {
  $folders = [];
  $files = [];

  //  Get immediate files
  $stmt = $pdo->prepare("SELECT id FROM files WHERE folder_id = ?");
  $stmt->execute([$folderId]);
  $files = $stmt->fetchAll(PDO::FETCH_COLUMN);

  //  Get immediate subfolders
  $stmt = $pdo->prepare("SELECT id FROM folders WHERE parent_id = ?");
  $stmt->execute([$folderId]);
  $subfolders = $stmt->fetchAll(PDO::FETCH_COLUMN);

  foreach ($subfolders as $subId) {
    $folders[] = $subId;

    //  Recurse into child folders
    $child = getFolderContentsRecursive($pdo, $subId);
    $folders = array_merge($folders, $child['folders']);
    $files = array_merge($files, $child['files']);
  }

  return ['folders' => $folders, 'files' => $files];
}

?>