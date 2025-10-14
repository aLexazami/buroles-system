<?php
function getEffectivePermissions(PDO $pdo, string $fileId, int $userId): array {
  $permissionMap = [
    'read'    => ['view'],
    'write'   => ['edit', 'comment', 'share', 'delete'],
    'comment' => ['comment'],
    'edit'    => ['edit', 'comment'],
    'view'    => ['view'], // legacy fallback
  ];

  // 🔍 Direct access
  $stmt = $pdo->prepare("
    SELECT permission
    FROM access_control
    WHERE file_id = ? AND user_id = ? AND is_revoked = FALSE
  ");
  $stmt->execute([$fileId, $userId]);
  $rawPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $capabilities = [];
  foreach ($rawPermissions as $perm) {
    $perm = strtolower($perm); // ✅ Normalize casing
    if (isset($permissionMap[$perm])) {
      $capabilities = array_merge($capabilities, $permissionMap[$perm]);
    }
  }

  if (!empty($capabilities)) {
    return array_values(array_unique($capabilities)); // ✅ Preserve order
  }

  // 🔁 Inherited access
  $parentId = getParentFolderId($pdo, $fileId);
  return $parentId ? getEffectivePermissions($pdo, $parentId, $userId) : [];
}

function getEffectivePermissionsWithSource(PDO $pdo, string $fileId, int $userId): array {
  $permissionMap = [
    'read'    => ['view'],
    'write'   => ['edit', 'comment', 'share', 'delete'],
    'comment' => ['comment'],
    'edit'    => ['edit', 'comment'],
    'view'    => ['view'], // legacy fallback
  ];

  // 🧠 Track visited nodes to prevent cycles
  $visited = [];
  $currentId = $fileId;

  while ($currentId && !in_array($currentId, $visited)) {
    $visited[] = $currentId;

    // 🔍 Check access at current level
    $stmt = $pdo->prepare("
      SELECT permission
      FROM access_control
      WHERE file_id = ? AND user_id = ? AND is_revoked = FALSE
    ");
    $stmt->execute([$currentId, $userId]);
    $rawPermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $capabilities = [];
    foreach ($rawPermissions as $perm) {
      $perm = strtolower($perm);
      if (isset($permissionMap[$perm])) {
        $capabilities = array_merge($capabilities, $permissionMap[$perm]);
      }
    }

    if (!empty($capabilities)) {
      return [
        'permissions'   => array_values(array_unique($capabilities)),
        'inheritedFrom' => $currentId === $fileId ? null : $currentId,
        'sourceType'    => $currentId === $fileId ? 'direct' : 'inherited'
      ];
    }

    // 🔁 Walk up the tree
    $stmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ?");
    $stmt->execute([$currentId]);
    $currentId = $stmt->fetchColumn();
  }

  // 🚫 No access found
  return [
    'permissions'   => [],
    'inheritedFrom' => null,
    'sourceType'    => 'none'
  ];
}

function getAccessListForItem(PDO $pdo, string $fileId): array {
  $stmt = $pdo->prepare("
    SELECT a.user_id, u.first_name, u.middle_name, u.last_name, u.email, u.avatar_path, a.permission
    FROM access_control a
    JOIN users u ON u.id = a.user_id
    WHERE a.file_id = ? AND a.is_revoked = FALSE
  ");
  $stmt->execute([$fileId]);

  $results = [];
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fullName = trim("{$row['first_name']} {$row['middle_name']} {$row['last_name']}");
    $results[] = [
      'user_id'    => $row['user_id'],
      'name'       => $fullName,
      'email'      => $row['email'],
      'avatar'     => $row['avatar_path'] ?: '/assets/img/default-avatar.png',
      'permission' => strtolower($row['permission'])
    ];
  }

  return $results;
}

function updateAccess(PDO $pdo, string $fileId, array $updates, ?array $newShare): void {
  // 🔄 Update existing permissions
  foreach ($updates as $entry) {
    $userId = $entry['user_id'] ?? null;
    $permission = $entry['permission'] ?? null;

    if ($userId && $permission) {
      if ($permission === 'revoke') {
        $stmt = $pdo->prepare("UPDATE access_control SET is_revoked = TRUE WHERE file_id = ? AND user_id = ?");
        $stmt->execute([$fileId, $userId]);
      } else {
        $stmt = $pdo->prepare("UPDATE access_control SET permission = ?, is_revoked = FALSE WHERE file_id = ? AND user_id = ?");
        $stmt->execute([$permission, $fileId, $userId]);
      }
    }
  }

  // ➕ Add new share
  if ($newShare && !empty($newShare['email']) && !empty($newShare['permission'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$newShare['email']]);
    $userId = $stmt->fetchColumn();

    if ($userId) {
      $stmt = $pdo->prepare("
        INSERT INTO access_control (file_id, user_id, permission, is_revoked)
        VALUES (?, ?, ?, FALSE)
        ON DUPLICATE KEY UPDATE permission = VALUES(permission), is_revoked = FALSE
      ");
      $stmt->execute([$fileId, $userId, $newShare['permission']]);
    }
  }
}

function getParentFolderId(PDO $pdo, string $fileId): ?string {
  $stmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ?");
  $stmt->execute([$fileId]);
  $parentId = $stmt->fetchColumn();
  return $parentId ?: null;
}

function isOwner(PDO $pdo, string $fileId, int $userId): bool {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$fileId, $userId]);
  return $stmt->fetchColumn() > 0;
}

// Dynamic REsolver Access in sharing to grant access also the  recursively nested folder and files
function hasAccess($pdo, $fileId, $userId) {
  // Step 1: Check direct access
  $stmt = $pdo->prepare("
    SELECT permission FROM access_control
    WHERE file_id = ? AND user_id = ? AND is_revoked = 0
    LIMIT 1
  ");
  $stmt->execute([$fileId, $userId]);
  if ($stmt->fetchColumn()) return true;

  // Step 2: Walk up the parent chain
  $currentId = $fileId;
  while ($currentId) {
    $stmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ?");
    $stmt->execute([$currentId]);
    $parentId = $stmt->fetchColumn();

    if (!$parentId) break;

    $stmt = $pdo->prepare("
      SELECT permission FROM access_control
      WHERE file_id = ? AND user_id = ? AND is_revoked = 0
      LIMIT 1
    ");
    $stmt->execute([$parentId, $userId]);
    if ($stmt->fetchColumn()) return true;

    $currentId = $parentId;
  }

  return false;
}