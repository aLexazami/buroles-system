<?php

/**
 * Get the absolute base directory for a user's uploads under a specific role.
 * Creates the directory if it doesn't exist.
 */
function getUploadBaseByRoleUser(string $roleId, string $userId): string {
  $baseDir = __DIR__ . "/../uploads/staff/$roleId/$userId";

  if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0755, true)) {
      logUploadError("Failed to create upload directory for role $roleId and user $userId");
      throw new RuntimeException("Upload base directory not found for role $roleId and user $userId");
    }
  }

  return $baseDir;
}

/**
 * Get the shared base directory for all staff uploads.
 * Creates the directory if it doesn't exist.
 */
function getStaffUploadBase(): string {
  $baseDir = __DIR__ . '/../uploads/staff';

  if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0755, true)) {
      logUploadError("Failed to create staff upload base directory");
      throw new RuntimeException("Staff upload base directory not found");
    }
  }

  return $baseDir;
}

/**
 * Log upload-related errors to a file.
 */
function logUploadError(string $message): void {
  $logFile = __DIR__ . '/../logs/upload_errors.log';
  $timestamp = date('Y-m-d H:i:s');
  error_log("[$timestamp] $message\n", 3, $logFile);
}

/**
 * Sanitize a path segment to prevent traversal and injection.
 * Now allows dots for versioned folder names like "v1.2".
 */
function sanitizeSegment(string $segment): string {
  return preg_replace('/[^a-zA-Z0-9_\-\. ]+/', '', $segment);
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
function resolveUploadPathFromBase(string $baseDir, string $parentPath, string $itemName): string {
  $safeParent = sanitizePath($parentPath);
  $safeItem   = ltrim(str_replace(['../', './'], '', $itemName), '/');
  $subPath    = $safeParent !== '' ? $safeParent . '/' . $safeItem : $safeItem;
  $fullPath   = $baseDir . '/' . $subPath;

  if (!file_exists($fullPath)) {
    error_log("resolveUploadPathFromBase: path not found → $fullPath");
  }

  return $fullPath;
}

/**
 * Generate a public-facing preview URL for a file.
 */
function resolvePreviewUrl(string $roleId, string $userId, string $folderPath, string $fileName): string {
  $safeFile = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $fileName);
  $safePath = trim($folderPath, '/');
  $subPath  = $safePath !== '' ? $safePath . '/' . $safeFile : $safeFile;

  return "/uploads/staff/$roleId/$userId/" . rawurlencode($subPath);
}

/**
 * Generate a preview URL for a user's file using their active role and ID.
 * Centralized for frontend use.
 */
function getUserUploadUrl(string $userId, string $folderPath, string $fileName): string {
  $roleId = '1'; // Default to staff role
  return resolvePreviewUrl($roleId, $userId, $folderPath, $fileName);
}
?>