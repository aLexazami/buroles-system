<?php
require_once __DIR__ . '/access-utils.php'; // getEffectivePermissionsWithSource()

function getSharedFolderContents(PDO $pdo, string $folderId, int $userId, bool $includeRoot = true): array {
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
      $access = getEffectivePermissionsWithSource($pdo, $folderId, $userId);
      if (!empty($access['permissions'])) {
        $root['permissions'] = $access['permissions'];
        $root['inherited_from'] = $access['inheritedFrom'];
        $root['source_type'] = $access['sourceType'];
        $root['size'] = getRecursiveFolderSize($pdo, $folderId, $userId);
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
    $access = getEffectivePermissionsWithSource($pdo, $file['id'], $userId);
    if (!empty($access['permissions'])) {
      $file['permissions'] = $access['permissions'];
      $file['inherited_from'] = $access['inheritedFrom'];
      $file['source_type'] = $access['sourceType'];

      if (!empty($file['parent_id'])) {
        $stmtParent = $pdo->prepare("SELECT name FROM files WHERE id = ?");
        $stmtParent->execute([$file['parent_id']]);
        $file['parent_name'] = $stmtParent->fetchColumn();
      }

      if ($file['type'] === 'folder') {
        $file['size'] = getRecursiveFolderSize($pdo, $file['id'], $userId);

        // 🧠 Prevent self-insertion in children
        $nested = getSharedFolderContents($pdo, $file['id'], $userId, false);
        $file['children'] = array_filter($nested, function ($child) use ($file) {
          return $child['id'] !== $file['id'];
        });
      }

      $visible[] = $file;
    }
  }

  return $visible;
}