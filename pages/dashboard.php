<?php
require_once __DIR__.'/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['active_role_id'])) {
    header("Location: ../index.php");
    exit();
}

switch ($_SESSION['active_role_id']) {
    case 1:
        include '/pages/main-staff.php';
        break;
    case 2:
        include '/pages/main-admin.php';
        break;
    case 99:
        include '/pages/main-super-admin.php';
        break;
    default:
        echo "⚠️ Unknown role. Please contact support.";
}
?>