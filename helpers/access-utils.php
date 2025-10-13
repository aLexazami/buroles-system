<?php
function getEffectivePermissions(PDO $pdo, string $fileId, int $userId): array {
  // 🔧 Permission mapping
  $permissionMap = [
    'read'    => ['view'],
    'write'   => ['edit', 'comment', 'share', 'delete'],
    'comment' => ['comment'],
    'edit'    => ['edit', 'comment'],
    'view'    => ['view'], // fallback for legacy
  ];

  // 🔍 Step 1: Check direct access
  $stmt = $pdo->prepare("
    SELECT permission
    FROM access_control
    WHERE file_id = ? AND user_id = ? AND is_revoked = FALSE
  ");
  $stmt->execute([$fileId, $userId]);
  $rawPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $capabilities = [];
  foreach ($rawPermissions as $perm) {
    if (isset($permissionMap[$perm])) {
      $capabilities = array_merge($capabilities, $permissionMap[$perm]);
    }
  }

  if (!empty($capabilities)) {
    return array_unique($capabilities); // ✅ Direct access wins
  }

  // 🔁 Step 2: Recursively check parent
  $stmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ?");
  $stmt->execute([$fileId]);
  $parentId = $stmt->fetchColumn();

  return $parentId ? getEffectivePermissions($pdo, $parentId, $userId) : [];
}

function getEffectivePermissionsWithSource(PDO $pdo, string $fileId, int $userId): array {
  // 🔧 Permission mapping
  $permissionMap = [
    'read'    => ['view'],
    'write'   => ['edit', 'comment', 'share', 'delete'],
    'comment' => ['comment'],
    'edit'    => ['edit', 'comment'],
    'view'    => ['view'], // legacy fallback
  ];

  // 🔍 Step 1: Check direct access
  $stmt = $pdo->prepare("
    SELECT permission
    FROM access_control
    WHERE file_id = ? AND user_id = ? AND is_revoked = FALSE
  ");
  $stmt->execute([$fileId, $userId]);
  $rawPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $capabilities = [];
  foreach ($rawPermissions as $perm) {
    if (isset($permissionMap[$perm])) {
      $capabilities = array_merge($capabilities, $permissionMap[$perm]);
    }
  }

  if (!empty($capabilities)) {
    return ['permissions' => array_unique($capabilities), 'inheritedFrom' => null];
  }

  // 🔁 Step 2: Recursively check parent
  $stmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ?");
  $stmt->execute([$fileId]);
  $parentId = $stmt->fetchColumn();

  if ($parentId) {
    $inherited = getEffectivePermissionsWithSource($pdo, $parentId, $userId);
    $inherited['inheritedFrom'] = $parentId;
    return $inherited;
  }

  return ['permissions' => [], 'inheritedFrom' => null];
}

