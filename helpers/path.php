<?php

/**
 * Get the absolute base directory for a user's uploads.
 */
function getUserUploadBase(string $userId): string {
  $baseDir = realpath(__DIR__ . '/../uploads/staff/' . $userId);
  if (!$baseDir) {
    throw new RuntimeException("Upload base directory not found for user: $userId");
  }
  return $baseDir;
}

/**
 * Sanitize a path segment to prevent traversal and injection.
 */
function sanitizeSegment(string $segment): string {
  return preg_replace('/[^a-zA-Z0-9_\- ]+/', '', $segment);
}

/**
 * Sanitize and normalize a full path string.
 */
function sanitizePath(string $path): string {
  $segments = array_filter(explode('/', trim($path, '/')));
  return implode('/', array_map('sanitizeSegment', $segments));
}

/**
 * Resolve the full absolute path for a file or folder inside a user's upload space.
 */
function resolveUploadPath(string $userId, string $parentPath, string $itemName): string {
  $baseDir = getUserUploadBase($userId);
  $safeParent = sanitizePath($parentPath);
  $safeItem = ltrim(str_replace(['../', './'], '', $itemName), '/');

  $subPath = $safeParent !== '' ? $safeParent . '/' . $safeItem : $safeItem;
  return $baseDir . '/' . $subPath;
}

/**
 * Resolve the absolute path for a folder (used in listing, deletion, etc.).
 */
function resolveFolderPath(string $userId, string $folderPath): string {
  $baseDir = getUserUploadBase($userId);
  $safePath = sanitizePath($folderPath);
  return $baseDir . '/' . $safePath;
}

/**
 * Generate a public-facing preview URL for a file.
 */
function resolvePreviewUrl(string $userId, string $folderPath, string $fileName): string {
  $safeFile = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $fileName);
  $safePath = trim($folderPath, '/');
  $subPath = $safePath !== '' ? $safePath . '/' . $safeFile : $safeFile;

  return "/uploads/staff/$userId/" . rawurlencode($subPath);
}
?>