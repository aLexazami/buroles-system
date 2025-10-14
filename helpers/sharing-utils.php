<?php
require_once __DIR__ . '/access-utils.php'; // getEffectivePermissionsWithSource()

function getSharedFolderContents(PDO $pdo, string $folderId, int $userId, bool $includeRoot = true, bool $asOwner = false): array {
  $visible = [];

  // 🧩 Include root folder if requested
  if ($includeRoot) {
    $stmtRoot = $pdo->prepare("
      SELECT f.*, u.first_name AS owner_first_name, u.last_name AS owner_last_name
      FROM files f
      JOIN users u ON f.owner_id = u.id
      WHERE f.id = :folderId AND f.is_deleted = FALSE
    ");
    $stmtRoot->execute([':folderId' => $folderId]);
    $root = $stmtRoot->fetch(PDO::FETCH_ASSOC);

    if ($root) {
      $access = [];

      if ($asOwner || $root['owner_id'] === $userId) {
        $access = [
          'permissions' => ['edit', 'comment', 'share', 'delete'],
          'inheritedFrom' => null,
          'sourceType' => 'owner'
        ];
      } else {
        $access = getEffectivePermissionsWithSource($pdo, $folderId, $userId);
      }

      if (!empty($access['permissions'])) {
        $root['permissions'] = $access['permissions'];
        $root['inherited_from'] = $access['inheritedFrom'] ?? null;
        $root['source_type'] = $access['sourceType'] ?? null;
        $root['size'] = getRecursiveFolderSize($pdo, $folderId);

        if ($root['type'] === 'folder') {
          $nested = getSharedFolderContents($pdo, $folderId, $userId, false, $asOwner);
          $root['children'] = array_filter($nested, function ($child) use ($folderId) {
            return $child['id'] !== $folderId;
          });
        }

        $visible[] = $root;
      }
    }
  }

  // 🔁 Recurse into children
  $stmt = $pdo->prepare("
    SELECT f.*, u.first_name AS owner_first_name, u.last_name AS owner_last_name
    FROM files f
    JOIN users u ON f.owner_id = u.id
    WHERE f.parent_id = :folderId AND f.is_deleted = FALSE
  ");
  $stmt->execute([':folderId' => $folderId]);
  $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($children as &$file) {
    $access = [];

    if ($asOwner || $file['owner_id'] === $userId) {
      $access = [
        'permissions' => ['edit', 'comment', 'share', 'delete'],
        'inheritedFrom' => null,
        'sourceType' => 'owner'
      ];
    } else {
      $access = getEffectivePermissionsWithSource($pdo, $file['id'], $userId);
    }

    if (!empty($access['permissions'])) {
      $file['permissions'] = $access['permissions'];
      $file['inherited_from'] = $access['inheritedFrom'] ?? null;
      $file['source_type'] = $access['sourceType'] ?? null;

      if (!empty($file['parent_id'])) {
        $stmtParent = $pdo->prepare("SELECT name FROM files WHERE id = ?");
        $stmtParent->execute([$file['parent_id']]);
        $file['parent_name'] = $stmtParent->fetchColumn();
      }

      if ($file['type'] === 'folder') {
        $file['size'] = getRecursiveFolderSize($pdo, $file['id']);

        $nested = getSharedFolderContents($pdo, $file['id'], $userId, false, $asOwner);
        $file['children'] = array_filter($nested, function ($child) use ($file) {
          return $child['id'] !== $file['id'];
        });
      }

      $visible[] = $file;
    }
  }

  return $visible;
}