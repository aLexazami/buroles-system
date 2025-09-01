<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_role'])) {
    $selected = (int) $_POST['selected_role'];

    if (in_array($selected, $_SESSION['available_roles'])) {
        $_SESSION['active_role_id'] = $selected;

        //  Set flag to indicate explicit switch
        $_SESSION['role_switched'] = true;
    }

    http_response_code(200);
    exit();
}
http_response_code(400);
?>