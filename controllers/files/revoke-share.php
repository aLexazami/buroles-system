<?php
require_once __DIR__ . '/../../helpers/sharing-utils.php';
$success = revokeShare($pdo, $_POST['type'], (int)$_POST['item_id'], (int)$_POST['shared_with']);
?>