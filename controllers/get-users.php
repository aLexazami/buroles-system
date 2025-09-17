<?php
$sortBy = $_GET['sort_by'] ?? 'id';
$sortOrder = $_GET['sort_order'] ?? 'asc';

$allowedSorts = ['id', 'username', 'email', 'role_name', 'last_name'];
$sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'last_name';
$sortOrder = ($sortOrder === 'desc') ? 'DESC' : 'ASC';

// Map sortBy to actual SQL column names
$columnMap = [
  'id' => 'users.id',
  'username' => 'users.username',
  'email' => 'users.email',
  'last_name' => 'users.last_name',
  'role_name' => 'roles.name'
];

$sortColumn = $columnMap[$sortBy] ?? 'users.last_name';

$stmt = $pdo->prepare("
  SELECT
    users.id,
    users.first_name,
    users.middle_name,
    users.last_name,
    users.username,
    users.email,
    users.must_change_password,
    users.is_locked,
    users.failed_attempts,
    roles.name AS role_name
  FROM users
  JOIN roles ON users.role_id = roles.id
  WHERE users.is_archived = 0
  ORDER BY $sortColumn $sortOrder
");

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>