<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
session_start();

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $password = htmlspecialchars(trim($_POST['password'] ?? ''));

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = 'Username and password are required.';
        $_SESSION['login_attempts']++;
        header("Location: ../index.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            SELECT users.*, roles.name AS role_name, roles.slug AS role_slug
            FROM users
            JOIN roles ON users.role_id = roles.id
            WHERE users.username = :username
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set session variables
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_archived']) {
                $_SESSION['error_message'] = 'Your account has been locked. Please contact the administrator.';
                $_SESSION['login_attempts']++;
                header("Location: ../index.php");
                exit();
            }
            $_SESSION['login_attempts'] = 0;
            $_SESSION['user_token'] = hash('sha256', $_ENV['SESSION_SECRET']);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['role_id']   = $user['role_id'];
            $_SESSION['firstName']  = $user['first_name'];
            $_SESSION['lastName']   = $user['last_name'];
            $_SESSION['role_name']  = $user['role_name'];
            $_SESSION['role_slug'] = strtolower($user['role_slug']); // Normalize casing

            $defaultRole = $user['role_id']; // always available from users table

            // Fetch all roles from user_roles
            $stmt = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);


            // Fallback if no user_roles exist
            if (empty($roles)) {
                $roles[] = $defaultRole;
            }

            // ✅ Set session context
            $_SESSION['available_roles'] = $roles;
            $_SESSION['active_role_id'] = $defaultRole;
            $_SESSION['default_role_id'] = $defaultRole;



            // 🔐 Redirect to password change if required
            if ($user['must_change_password']) {
                header("Location: ../pages/change-password.php");
                exit();
            }

            // Role-based redirection using active_role_id
            $roleRedirects = [
                1 => '../pages/main-staff.php',
                2 => '../pages/main-admin.php',
                99 => '../pages/main-super-admin.php',
            ];

            $activeRole = $_SESSION['active_role_id'] ?? null;

            if (isset($roleRedirects[$activeRole])) {
                header("Location: " . $roleRedirects[$activeRole]);
            } else {
                $_SESSION['error_message'] = 'User role not recognized.';
                header("Location: ../index.php");
            }
            exit;
        } else {
            $_SESSION['error_message'] = 'Invalid username or password.';
            $_SESSION['login_attempts']++;
            header("Location: ../index.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = 'An error occurred. Please try again later.';
        $_SESSION['login_attempts']++;
        header("Location: ../index.php");
        exit();
    }
}
