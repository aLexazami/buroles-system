<?php
session_start();
session_unset();
session_destroy();

// Optional: reset login_attempts explicitly
$_SESSION['login_attempts'] = 0;

header("Location: ../index.php");
exit();
?>