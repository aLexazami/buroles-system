<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT id, level, label FROM grade_levels ORDER BY level ASC");
$gradeLevels = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['gradeLevels' => $gradeLevels]);