<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
session_start();

// Track login attempts for UI display
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

        if (!$user) {
            $_SESSION['error_message'] = 'Invalid username or password.';
            $_SESSION['login_attempts']++;
            header("Location: ../index.php");
            exit();
        }

        // 🔒 Check if account is archived or locked
        if ($user['is_archived']) {
            $_SESSION['error_message'] = 'Your account has been archived. Please contact the administrator.';
            header("Location: ../index.php");
            exit();
        }

        if ($user['is_locked']) {
            $_SESSION['error_message'] = 'Your account is locked due to multiple failed login attempts.';
            header("Location: ../index.php");
            exit();
        }

        // 🔐 Verify password
        if (password_verify($password, $user['password'])) {
            // ✅ Reset failed attempts
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0 WHERE id = ?");
            $stmt->execute([$user['id']]);

            $_SESSION['login_attempts'] = 0;
            $_SESSION['user_token']     = hash('sha256', $_ENV['SESSION_SECRET']);
            $_SESSION['user_id']        = $user['id'];
            $_SESSION['username']       = $user['username'];
            $_SESSION['email']          = $user['email'];
            $_SESSION['role_id']        = $user['role_id'];
            $_SESSION['firstName']      = $user['first_name'];
            $_SESSION['middleName']     = $user['middle_name'];
            $_SESSION['lastName']       = $user['last_name'];
            $_SESSION['role_name']      = $user['role_name'];
            $_SESSION['role_slug']      = strtolower($user['role_slug']);
            $_SESSION['original_role_id']   = $user['role_id'];
            $_SESSION['original_role_name'] = $user['role_name'];
            $_SESSION['avatar_path'] = $user['avatar_path'];

            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role_id' => $user['role_id'],
                'role_name' => $user['role_name'],
                'role_slug' => strtolower($user['role_slug']),
                'first_name' => $user['first_name'],
                'middle_name' => $user['middle_name'],
                'last_name' => $user['last_name'],
                'avatar_path' => $user['avatar_path']
            ];



            // 🔄 Fetch all roles from user_roles
            $stmt = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($roles)) {
                $roles[] = $user['role_id'];
            }

            $_SESSION['available_roles'] = $roles;
            $_SESSION['active_role_id']  = $user['role_id'];
            $_SESSION['default_role_id'] = $user['role_id'];
            $_SESSION['role_switched']   = false;

            // 🔐 Redirect to password change if required
            if ($user['must_change_password']) {
                header("Location: ../pages/change-password.php");
                exit();
            }

            // 🚀 Role-based redirection
            $roleRedirects = [
                1  => '../pages/main-staff.php',
                2  => '../pages/main-admin.php',
                99 => '../pages/main-super-admin.php',
            ];

            $activeRole = $_SESSION['active_role_id'] ?? null;

            if (isset($roleRedirects[$activeRole])) {
                header("Location: " . $roleRedirects[$activeRole]);
            } else {
                $_SESSION['error_message'] = 'User role not recognized.';
                header("Location: ../index.php");
            }
            exit();
        } else {
            // ❌ Handle failed login
            $newAttempts = $user['failed_attempts'] + 1;

            if ($newAttempts >= 3) {
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, is_locked = 1 WHERE id = ?");
                $stmt->execute([$newAttempts, $user['id']]);
                $_SESSION['error_message'] = 'Your account has been locked due to 3 failed login attempts.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                $stmt->execute([$newAttempts, $user['id']]);
                $_SESSION['error_message'] = "Please Check your username or password.";
            }

            $_SESSION['login_attempts']++;
            header("Location: ../index.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error_message'] = 'An error occurred. Please try again later.';
        $_SESSION['login_attempts']++;
        header("Location: ../index.php");
        exit();
    }
}
