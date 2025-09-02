<?php
session_start();
require_once __DIR__ . '/../config/database.php'; // Ensure $pdo is available

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_role'])) {
    $selected = (int) $_POST['selected_role'];

    if (in_array($selected, $_SESSION['available_roles'])) {
        $_SESSION['active_role_id'] = $selected;
        $_SESSION['role_switched'] = true;

        // 🔄 Refresh role_slug based on selected role
        $stmt = $pdo->prepare("SELECT slug FROM roles WHERE id = ?");
        $stmt->execute([$selected]);
        $role = $stmt->fetch();

        if ($role && !empty($role['slug'])) {
            $_SESSION['role_slug'] = strtolower($role['slug']);
        }
    }

    http_response_code(200);
    exit();
}
http_response_code(400);
?>