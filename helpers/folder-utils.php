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

function getFolderSize(string $folderPath): int {
  $size = 0;
  foreach (scandir($folderPath) as $item) {
    $fullPath = $folderPath . '/' . $item;
    if (is_file($fullPath)) {
      $size += filesize($fullPath);
    }
  }
  return $size;
}

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
 * Count only files inside a folder (excluding subfolders).
 */
function countFilesInFolder(string $folderPath): int {
  if (!is_dir($folderPath)) return 0;

  return count(array_filter(scandir($folderPath), fn($item) =>
    is_file($folderPath . '/' . $item) && $item !== '.' && $item !== '..'
  ));
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
  return !file_exists($target) ? mkdir($target, 0755, true) : false;
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

?>