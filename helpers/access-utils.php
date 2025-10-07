<?php
require_once __DIR__ . '/folder-utils.php';
require_once __DIR__ . '/file-utils.php';
require_once __DIR__ . '/sharing-utils.php';
require_once __DIR__ . '/label-utils.php'; // if you have getAccessLabel/getAccessColor here
require_once __DIR__ . '/logger.php';

function resolveItemAccess(PDO $pdo, int $userId, string $type, string $path, int $ownerId): array|false
{
  // 🚨 Detect corrupted or mixed paths
  if (str_contains($path, 'C:\\') || str_contains($path, '/helpers/')) {
    logDebug("❌ Path corruption detected in access check → $path");
  }

  $table = $type === 'file' ? 'files' : 'folders';

  // 🔍 Try exact match first
  $stmt = $pdo->prepare("SELECT id, owner_id FROM $table WHERE path = ? AND owner_id = ?");
  $stmt->execute([$path, $ownerId]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);

  $resolvedVia = 'direct';

  // ❌ If item doesn't exist, fallback to parent path
  if (!$item) {
    $segments = explode('/', trim($path, '/'));
    while (count($segments) > 1) {
      array_pop($segments);
      $parentPath = 'uploads/staff/' . $ownerId . '/' . implode('/', $segments);

      $stmt->execute([$parentPath, $ownerId]);
      $item = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($item) {
        $type = 'folder'; // fallback is always folder
        $resolvedVia = "inherited from $parentPath";
        logDebug("Access fallback: $path → $parentPath");
        break;
      }
    }

    if (!$item) {
      logDebug("Access failed: $path → no matching item or parent");
      return false;
    }
  }

  $itemId = (int)$item['id'];
  $actualOwnerId = (int)$item['owner_id'];

  // ✅ If viewer is owner, override access
  $accessLevel = ($userId === $actualOwnerId)
    ? 'owner'
    : getSharedAccess($pdo, $userId, $type, $itemId);

  logDebug("Access resolved for $path → $resolvedVia → level=$accessLevel");

  return [
    'itemId'      => $itemId,
    'accessLevel' => $accessLevel,
    'accessLabel' => getAccessLabel($accessLevel),
    'accessColor' => getAccessColor($accessLevel ?: 'none'),
    'canEdit'     => in_array($accessLevel, ['owner', 'editor'], true),
    'canComment'  => in_array($accessLevel, ['owner', 'editor', 'comment'], true),
  ];
}

function getAccessByPath(PDO $pdo, int $viewerId, string $path, string $type): string|false
{
  $table = $type === 'file' ? 'files' : 'folders';
  $stmt = $pdo->prepare("SELECT id FROM $table WHERE path = ?");
  $stmt->execute([$path]);
  $itemId = $stmt->fetchColumn();
  return $itemId ? getSharedAccess($pdo, $viewerId, $type, (int)$itemId) : false;
}

function getSharedAccessCached(PDO $pdo, int $userId, string $type, int $itemId, array &$cache): string|false
{
  $key = "$type:$itemId";
  if (isset($cache[$key])) return $cache[$key];
  return $cache[$key] = getSharedAccess($pdo, $userId, $type, $itemId);
}

function resolveUserId(PDO $pdo, string $email): int|false
{
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->execute([$email]);
  return $stmt->fetchColumn() ?: false;
}