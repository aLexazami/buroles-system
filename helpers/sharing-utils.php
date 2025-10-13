<?php
function getSharedFolderContents(PDO $pdo, string $folderId, int $userId): array {
  $stmt = $pdo->prepare("
    SELECT *
    FROM files
    WHERE parent_id = :folderId
      AND is_deleted = FALSE
  ");
  $stmt->execute([':folderId' => $folderId]);
  $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $accessible = [];

  foreach ($children as $child) {
    $access = getEffectivePermissionsWithSource($pdo, $child['id'], $userId);
    if (!empty($access['permissions'])) {
      $child['permissions'] = $access['permissions'];
      $child['inherited_from'] = $access['inheritedFrom'];

      // 📦 If folder, recursively fetch children
      if ($child['type'] === 'folder') {
        $child['children'] = getSharedFolderContents($pdo, $child['id'], $userId);
        $child['size'] = getRecursiveFolderSize($pdo, $child['id'], $userId);
      }

      $accessible[] = $child;
    }
  }

  return $accessible;
}