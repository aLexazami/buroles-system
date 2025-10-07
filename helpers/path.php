<?php

/**
 * Map role ID to folder slug.
 */
function getRoleSlug(string $roleId): string {
  return match ($roleId) {
    '1' => 'staff',
    '2' => 'admin',
    '3' => 'super-admin',
    default => 'unknown'
  };
}

function getUploadBaseByRoleUser(string $roleId, string $userId): string {
  return getUploadBasePathOnly($roleId, $userId);
}

/**
 * Get the absolute base path for a user's uploads under a specific role.
 * Does NOT create the directory.
 */
function getUploadBasePathOnly(string $roleId, string $userId): string {
  $roleSlug = getRoleSlug($roleId);
  return __DIR__ . "/../uploads/$roleSlug/$userId";
}

/**
 * Explicitly create the user's upload directory if needed.
 */
function ensureUploadBaseExists(string $roleId, string $userId): void {
  $baseDir = getUploadBasePathOnly($roleId, $userId);
  if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0755, true)) {
      logUploadError("Failed to create upload directory for role $roleId and user $userId");
      throw new RuntimeException("Upload base directory not found for role $roleId and user $userId");
    }
  }
}

/**
 * Get the shared base path for all staff uploads.
 * Does NOT create the directory.
 */
function getStaffUploadBasePathOnly(): string {
  return __DIR__ . '/../uploads/staff';
}

/**
 * Explicitly create the staff upload base directory if needed.
 */
function ensureStaffUploadBaseExists(): void {
  $baseDir = getStaffUploadBasePathOnly();
  if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0755, true)) {
      logUploadError("Failed to create staff upload base directory");
      throw new RuntimeException("Staff upload base directory not found");
    }
  }
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
 * Does NOT create any directories.
 */
function resolveUploadPathFromBase(string $baseDir, string $parentPath, string $itemName): string {
  $safeParent = sanitizePath($parentPath);
  $safeItem   = sanitizeSegment($itemName);

  // 🛡️ Guard against corrupted input
  if (str_contains($safeParent, 'uploads/') || str_contains($safeParent, 'C:\\') || str_contains($safeParent, '\\uploads\\')) {
    error_log("❌ resolveUploadPathFromBase: corrupted parentPath → $safeParent");
    return '';
  }

  $subPath  = $safeParent !== '' ? "$safeParent/$safeItem" : $safeItem;
  $fullPath = $baseDir . '/' . $subPath;

  if (!file_exists($fullPath)) {
    error_log("resolveUploadPathFromBase: path not found → $fullPath");
  }

  return $fullPath;
}

/**
 * Resolve full path using role and user ID.
 * Does NOT create any directories.
 */
function resolveUploadPath(string $roleId, string $userId, string $parentPath, string $itemName): string {
  $baseDir = getUploadBasePathOnly($roleId, $userId);
  return resolveUploadPathFromBase($baseDir, $parentPath, $itemName);
}

/**
 * Generate a public-facing preview URL for a file.
 */
function resolvePreviewUrl(string $roleId, string $userId, string $folderPath, string $fileName): string {
  $roleSlug = getRoleSlug($roleId);
  $safeFile = sanitizeSegment($fileName);
  $safePath = sanitizePath($folderPath);
  $subPath  = $safePath !== '' ? "$safePath/$safeFile" : $safeFile;

  return "/uploads/$roleSlug/$userId/" . rawurlencode($subPath);
}

/**
 * Generate a preview URL for a user's file using their active role and ID.
 */
function getUserUploadUrl(string $roleId, string $userId, string $folderPath, string $fileName): string {
  return resolvePreviewUrl($roleId, $userId, $folderPath, $fileName);
}

/**
 * Get item ID (file or folder) by its path and owner.
 */
function getItemIdByPath(PDO $pdo, string $path, int $ownerId, bool $isFolder): ?int {
  $table  = $isFolder ? 'folders' : 'files';
  $column = 'path';

  $stmt = $pdo->prepare("SELECT id FROM $table WHERE $column = ? AND owner_id = ?");
  $stmt->execute([$path, $ownerId]);
  $id = $stmt->fetchColumn();

  return $id ?: null;
}

function getScopedPath(string $roleId, string $userId, string $relativePath): string {
  $roleSlug = getRoleSlug($roleId);
  return "uploads/$roleSlug/$userId/" . ltrim($relativePath, '/');
}

// helpers/path-utils.php
function extractRelativePath(string $scopedPath, string $userId): string {
  $prefix = "uploads/staff/$userId/";
  return str_starts_with($scopedPath, $prefix)
    ? ltrim(substr($scopedPath, strlen($prefix)), '/')
    : $scopedPath;
}

function normalizeRelativePath(string $userId, string $path): string {
  $expectedPrefix = "uploads/staff/$userId/";
  return str_starts_with($path, $expectedPrefix)
    ? substr($path, strlen($expectedPrefix))
    : $path;
}