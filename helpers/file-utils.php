<?php

/**
 * Get icon filename for a given file extension.
 */
function getFileIcon(string $filename): string {
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $iconMap = [
    'pdf'   => 'pdf.png',
    'doc'   => 'doc.png',
    'docx'  => 'doc.png',
    'jpg'   => 'image.png',
    'jpeg'  => 'image.png',
    'png'   => 'image.png',
    'gif'   => 'image.png',
    'zip'   => 'zip.png',
    'rar'   => 'zip.png',
  ];

  return "/assets/img/icons/" . ($ext ? ($iconMap[$ext] ?? 'file.png') : 'file.png');
}

/**
 * Count all files inside a directory recursively.
 */
function countUserFiles(string $basePath): int {
  if (!is_dir($basePath)) return 0;

  $count = 0;
  $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));

  foreach ($rii as $file) {
    if ($file->isFile()) $count++;
  }

  return $count;
}

function getFileIdByPath(PDO $pdo, string $path, int $ownerId): int|false
{
  static $fileCache = [];

  // 🛡️ Corruption check
  if (str_contains($path, 'C:\\') || str_contains($path, '/helpers/')) {
    error_log("❌ Path corruption detected in getFileIdByPath → $path");
  }

  $cacheKey = "$ownerId:$path";
  if (isset($fileCache[$cacheKey])) return $fileCache[$cacheKey];

  $stmt = $pdo->prepare("SELECT id FROM files WHERE path = ? AND owner_id = ? LIMIT 1");
  $stmt->execute([$path, $ownerId]);
  $fileId = $stmt->fetchColumn();

  return $fileCache[$cacheKey] = $fileId ? (int)$fileId : false;
}
/* ****************************************************************************************** */
function getFilesForView($userId, $view = 'my-files', $folderId = null) {
  global $pdo;

  switch ($view) {
    case 'shared-with-me':
      $sql = "SELECT f.*, ac.permission
              FROM access_control ac
              JOIN files f ON ac.file_id = f.id
              WHERE ac.user_id = :userId
                AND ac.is_revoked = FALSE
                AND f.is_deleted = FALSE
                AND f.owner_id != :userId";
      break;

    case 'shared-by-me':
      $sql = "SELECT f.*, ac.user_id AS shared_with, ac.permission
              FROM access_control ac
              JOIN files f ON ac.file_id = f.id
              WHERE ac.granted_by = :userId
                AND ac.is_revoked = FALSE
                AND f.is_deleted = FALSE";
      break;

    default: // 'my-files'
      $sql = "SELECT * FROM files
              WHERE owner_id = :userId
                AND is_deleted = FALSE";
      if ($folderId) {
        $sql .= " AND parent_id = :folderId";
      } else {
        $sql .= " AND parent_id IS NULL";
      }
  }

  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
  if ($view === 'my-files' && $folderId) {
    $stmt->bindValue(':folderId', $folderId);
  }
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function canPerformAction($userId, $fileId, $action) {
  global $pdo;

  // Check ownership
  $stmt = $pdo->prepare("SELECT owner_id FROM files WHERE id = ?");
  $stmt->execute([$fileId]);
  $ownerId = $stmt->fetchColumn();
  if ($ownerId == $userId) return true;

  // Check direct access
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM access_control
                         WHERE user_id = ? AND file_id = ? AND permission = ? AND is_revoked = FALSE");
  $stmt->execute([$userId, $fileId, $action]);
  return $stmt->fetchColumn() > 0;
}


function formatSize($bytes) {
  if ($bytes < 1024) return $bytes . ' B';
  if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
  return round($bytes / 1048576, 1) . ' MB';
}

function formatDate($timestamp) {
  return date('M d, Y', strtotime($timestamp));
}
?>