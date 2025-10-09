<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php'; // isValidUuid()

$folderId = $_GET['folder_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

// 🔐 Validate session
if (!$userId) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// 📁 Normalize folderId
if (!isValidUuid($folderId)) {
  $folderId = null; // Treat as root view
}

// 📄 Prepare query with aggregated permissions
$query = "
  SELECT 
    f.id, f.name, f.type, f.size, f.updated_at, f.path, f.mime_type, f.owner_id,
    u.first_name AS owner_first_name,
    u.last_name AS owner_last_name,
    GROUP_CONCAT(ac.permission) AS permissions
  FROM files f
  JOIN users u ON f.owner_id = u.id
  LEFT JOIN access_control ac 
    ON ac.file_id = f.id AND ac.user_id = ?
  WHERE f.parent_id " . ($folderId ? "= ?" : "IS NULL") . "
    AND f.is_deleted = FALSE
  GROUP BY f.id
";

$params = $folderId ? [$userId, $folderId] : [$userId];
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🧠 Format output with ownership logic
$items = array_map(function ($row) use ($userId) {
  $isOwner = $row['owner_id'] === $userId;

  return [
    'id' => $row['id'],
    'name' => $row['name'],
    'type' => $row['type'],
    'size' => $row['size'],
    'updated_at' => $row['updated_at'],
    'path' => $row['path'],
    'mime_type' => $row['mime_type'], // ✅ Include MIME type
    'owner_first_name' => $row['owner_first_name'],
    'owner_last_name' => $row['owner_last_name'],
    'permissions' => $isOwner
      ? ['read', 'comment', 'share', 'delete']
      : ($row['permissions'] ? explode(',', $row['permissions']) : [])
  ];
}, $rows);

// 📤 Return JSON
header('Content-Type: application/json');
echo json_encode($items);