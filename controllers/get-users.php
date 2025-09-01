<?php
$stmt = $pdo->query("
    SELECT
  users.id,
  users.first_name,
  users.middle_name,
  users.last_name,
  users.username,
  users.email,
  users.must_change_password,
  roles.name AS role_name
FROM users
JOIN roles ON users.role_id = roles.id
WHERE users.is_archived = 0
ORDER BY users.last_name ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>