<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/logging-utils.php';

$query = trim($_GET['query'] ?? '');
$exclude = trim($_GET['exclude'] ?? '');

if (strlen($query) < 1) {
  echo json_encode([]);
  exit;
}

// ✅ Log the suggestion fetch attempt
logSuggestionFetch($query, $exclude);

try {
  $sql = "SELECT email, avatar_path AS avatar,
                 CONCAT(first_name, ' ', last_name) AS name,
                 role_id
          FROM users
          WHERE role_id = 1
            AND is_archived = 0
            AND LOWER(email) LIKE LOWER(:likeQuery)";

  // Apply exclusion if provided
  if (!empty($exclude)) {
    $sql .= " AND LOWER(email) != LOWER(:exclude)";
  }

  $sql .= " ORDER BY email ASC LIMIT 10";

  $stmt = $pdo->prepare($sql);

  $params = ['likeQuery' => "%$query%"];
  if (!empty($exclude)) {
    $params['exclude'] = $exclude;
  }

  $stmt->execute($params);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $normalized = array_map(function ($user) {
    return [
      'email' => htmlspecialchars($user['email']),
      'name' => htmlspecialchars($user['name']),
      'avatar' => !empty($user['avatar']) ? $user['avatar'] : '/assets/img/default-avatar.png',
      'role_id' => (int) $user['role_id']
    ];
  }, $results);

  echo json_encode($normalized);
} catch (Exception $e) {
  error_log("Staff search error: " . $e->getMessage());
  echo json_encode([]);
}