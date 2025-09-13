<?php

/**
 * Scan a directory and return structured metadata for folders and files.
 */
function listFolderItems(string $path): array {
  if (!is_dir($path)) return ['folders' => [], 'files' => []];

  $folders = [];
  $files = [];

  foreach (scandir($path) as $item) {
    if ($item === '.' || $item === '..') continue;

    $fullPath = $path . '/' . $item;

    if (is_dir($fullPath)) {
      $folders[] = buildFolderMetadata($item, $fullPath);
    } elseif (is_file($fullPath)) {
      $files[] = buildFileMetadata($item, $fullPath);
    }
  }

  return ['folders' => $folders, 'files' => $files];
}

/**
 * Build metadata for a folder.
 */
function buildFolderMetadata(string $name, string $fullPath): array {
  return [
    'name' => $name,
    'path' => $fullPath,
    'modified' => formatModifiedTime($fullPath),
    'fileCount' => countFilesInFolder($fullPath),
    'size' => getFolderSize($fullPath)
  ];
}

/**
 * Build metadata for a file.
 */
function buildFileMetadata(string $name, string $fullPath): array {
  return [
    'name' => $name,
    'path' => $fullPath,
    'modified' => formatModifiedTime($fullPath),
    'size' => filesize($fullPath)
  ];
}

/**
 * Get total size of a folder including nested contents.
 */
function getFolderSize(string $folderPath): int {
  $size = 0;

  if (!is_dir($folderPath)) return 0;

  foreach (scandir($folderPath) as $item) {
    if ($item === '.' || $item === '..') continue;

    $fullPath = $folderPath . '/' . $item;

    if (is_file($fullPath)) {
      $size += filesize($fullPath);
    } elseif (is_dir($fullPath)) {
      $size += getFolderSize($fullPath);
    }
  }

  return $size;
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
  return date("M d, Y H:i", filemtime($path));
}

/**
 * Count only files inside a folder (including nested).
 */
function countFilesInFolder(string $folderPath): int {
  $count = 0;

  if (!is_dir($folderPath)) return 0;

  foreach (scandir($folderPath) as $item) {
    if ($item === '.' || $item === '..') continue;

    $fullPath = $folderPath . '/' . $item;

    if (is_file($fullPath)) {
      $count++;
    } elseif (is_dir($fullPath)) {
      $count += countFilesInFolder($fullPath);
    }
  }

  return $count;
}

/**
 * Create a folder safely inside a base path.
 * Supports nested folders like "Reports/2025/September".
 */
function createFolder(string $basePath, string $folderPath): bool {
  $segments = explode('/', trim($folderPath, '/'));
  $safeSegments = array_filter(array_map('sanitizeSegment', $segments));

  if (empty($safeSegments)) return false;

  $target = rtrim($basePath, '/') . '/' . implode('/', $safeSegments);

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
  if (!is_dir($folderPath)) return false;

  foreach (scandir($folderPath) as $item) {
    if ($item === '.' || $item === '..') continue;

    $fullItem = $folderPath . '/' . $item;
    is_dir($fullItem)
      ? deleteFolderRecursive($fullItem)
      : unlink($fullItem);
  }

  return rmdir($folderPath);
}

/**
 * Recursively move a folder and its contents to a new location.
 * Used as a fallback when rename() fails on non-empty directories.
 */
function moveFolderRecursively(string $source, string $destination): bool {
  if (!is_dir($source)) return false;
  if (!mkdir($destination, 0755, true)) {
    error_log("moveFolderRecursively: failed to create destination → $destination");
    return false;
  }

  foreach (scandir($source) as $item) {
    if ($item === '.' || $item === '..') continue;

    $src = $source . '/' . $item;
    $dst = $destination . '/' . $item;

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
?>