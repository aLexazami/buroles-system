<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("
  SELECT s.id, s.name, sc.name AS category
  FROM services s
  JOIN service_categories sc ON s.category_id = sc.id
  ORDER BY sc.name, s.name
");

$optgroups = [];
$options = [];

while ($row = $stmt->fetch()) {
  $optgroups[$row['category']] = true;
  $options[] = [
    'id' => $row['id'],
    'name' => $row['name'],
    'category' => $row['category']
  ];
}

echo json_encode([
  'optgroups' => array_map(fn($label) => ['value' => $label, 'label' => $label], array_keys($optgroups)),
  'options' => $options
]);