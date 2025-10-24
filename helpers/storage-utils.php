<?php
function ensureUserStorageRow(PDO $pdo, int $userId): void {
  // Check if the user already has a storage row
  $stmt = $pdo->prepare("SELECT 1 FROM user_storage WHERE user_id = ?");
  $stmt->execute([$userId]);

  if (!$stmt->fetchColumn()) {
    // Optional: fetch role or plan to assign tiered limits
    $defaultLimit = 5368709120; // 5GB

    $insert = $pdo->prepare("INSERT INTO user_storage (user_id, storage_limit) VALUES (?, ?)");
    $insert->execute([$userId, $defaultLimit]);
  }
}

function canUploadFile(PDO $pdo, int $userId, int $fileSize): array {
  ensureUserStorageRow($pdo, $userId);

  $stmt = $pdo->prepare("SELECT storage_used, storage_limit FROM user_storage WHERE user_id = ?");
  $stmt->execute([$userId]);
  $storage = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$storage) {
    return [
      'allowed' => false,
      'reason' => 'Storage quota not initialized.',
      'used' => 0,
      'limit' => 0,
      'remaining' => 0,
      'percent_used' => 0
    ];
  }

  $used = (int) $storage['storage_used'];
  $limit = (int) $storage['storage_limit'];
  $remaining = $limit - $used;
  $percentUsed = round(($used / $limit) * 100, 2);
  $wouldExceed = ($used + $fileSize) > $limit;

  return [
    'allowed' => !$wouldExceed,
    'reason' => $wouldExceed ? 'Storage limit exceeded.' : 'Upload allowed.',
    'used' => $used,
    'limit' => $limit,
    'remaining' => $remaining,
    'percent_used' => $percentUsed,
    'warning' => $percentUsed >= 90
  ];
}

function formatStorageSize(int $bytes): string {
  if ($bytes < 1024) {
    return $bytes . ' B';
  } elseif ($bytes < 1024 ** 2) {
    return round($bytes / 1024, 2) . ' KB';
  } elseif ($bytes < 1024 ** 3) {
    return round($bytes / (1024 ** 2), 2) . ' MB';
  } else {
    return round($bytes / (1024 ** 3), 2) . ' GB';
  }
}

function getStorageStats(PDO $pdo, int $userId): array {
  ensureUserStorageRow($pdo, $userId);

  $stmt = $pdo->prepare("SELECT storage_used, storage_limit FROM user_storage WHERE user_id = ?");
  $stmt->execute([$userId]);
  $storage = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$storage) {
    return [
      'used_bytes' => 0,
      'limit_bytes' => 5368709120,
      'used_display' => '0 B',
      'limit_display' => '5 GB',
      'used_gb' => 0,
      'limit_gb' => 5,
      'percent_used' => 0,
      'warning' => false
    ];
  }

  $used = (int) $storage['storage_used'];
  $limit = (int) $storage['storage_limit'];
  $percent = round(($used / $limit) * 100, 2);
  $usedGB = $used / (1024 ** 3);
  $limitGB = $limit / (1024 ** 3);

  return [
    'used_bytes' => $used,
    'limit_bytes' => $limit,
    'used_display' => formatStorageSize($used),
    'limit_display' => formatStorageSize($limit),
    'used_gb' => $usedGB,
    'limit_gb' => $limitGB,
    'percent_used' => $percent,
    'warning' => $percent >= 90
  ];
}