<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Resolve the full virtual path of a folder based on its parent.
 */
function resolveFolderPath(PDO $pdo, ?string $folderId, int $userId): ?string {
  if (!$folderId) return null;

  $stmt = $pdo->prepare("SELECT path FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$folderId, $userId]);
  return $stmt->fetchColumn() ?: null;
}

/**
 * Build the full virtual path for a folder or file.
 * Always uses /srv/burol-storage/[userId]/... as the base.
 */
function buildVirtualPath(?string $parentPath, string $userId, string $uuid): string {
  return $parentPath ? rtrim($parentPath, '/') . '/' . $uuid : "/srv/burol-storage/$userId/$uuid";
}

/**
 * Convert a virtual path to a physical disk path.
 */
function resolveDiskPath(string $virtualPath): string {
  return __DIR__ . '/../' . ltrim($virtualPath, '/');
}

/**
 * Ensure the directory exists on disk.
 */
function ensureDirectoryExists(string $diskPath): void {
  if (!is_dir($diskPath)) mkdir($diskPath, 0775, true);
}

/**
 * Recursively ensure all parent folders in a virtual path exist on disk.
 */
function ensureVirtualPathExists(string $virtualPath): void {
  $diskPath = resolveDiskPath(dirname($virtualPath));
  ensureDirectoryExists($diskPath);
}

