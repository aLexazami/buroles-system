<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? null;

// Optional: validate allowed customer types
$validTypes = ['Citizen', 'Business', 'Government'];
if (!$type || !in_array($type, $validTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid customer type']);
    exit;
}

try {
    $services = getServicesByCustomerType($pdo, $type);
    echo json_encode($services);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>