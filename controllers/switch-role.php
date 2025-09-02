<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// 🧱 Reusable JSON response helper
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// 🔐 Validate session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['available_roles'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

// 🧪 Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['selected_role'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
}

$selected = (int) $_POST['selected_role'];

// 🔍 Check if selected role is allowed
if (!in_array($selected, $_SESSION['available_roles'])) {
    jsonResponse(['success' => false, 'message' => 'Role not permitted'], 403);
}

// 🔄 Refresh role context
$_SESSION['active_role_id'] = $selected;
$_SESSION['role_switched'] = true;

$stmt = $pdo->prepare("SELECT slug, name FROM roles WHERE id = ?");
$stmt->execute([$selected]);
$role = $stmt->fetch();

if ($role && !empty($role['slug'])) {
    $_SESSION['role_slug'] = strtolower($role['slug']);
    $_SESSION['active_role_name'] = $role['name']; // ✅ This reflects the switched role

    // ✅ Preserve original role name if not already set
    if (!isset($_SESSION['original_role_name'])) {
        $_SESSION['original_role_name'] = $_SESSION['role_name'] ?? $role['name'];
    }

    jsonResponse([
        'success' => true,
        'active_role' => $role['name'],
        'original_role' => $_SESSION['original_role_name']
    ]);
} else {
    jsonResponse(['success' => false, 'message' => 'Role not found'], 404);
}