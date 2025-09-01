<?php
<<<<<<< HEAD
require_once __DIR__ . '/../includes/bootstrap.php'; // Loads Dotenv and Composer autoload

session_start();

// 🔧 Public pages that require no authentication
const PUBLIC_PAGES = [
    'index.php',
    'login.php',
    'forgot-password.php',
    'feedback-form.php',
    'faqs.php'
];

// 🔧 Role-based access map
const ROLE_ACCESS = [
    'admin'        => ['main-admin.php', 'admin-tools.php'],
    'staff'        => ['main-staff.php', 'staff-dashboard.php'],
    'super_admin'  => ['main-super-admin.php', 'super-settings.php']
];

// 🧠 Determine current page
$currentPage = basename($_SERVER['PHP_SELF']);

// ✅ Allow public pages without validation
if (in_array($currentPage, PUBLIC_PAGES, true)) {
    return;
}

// ✅ Validate session essentials
if (
    empty($_SESSION['username']) ||
    empty($_SESSION['role_name']) ||
    empty($_SESSION['user_token'])
) {
=======
require_once __DIR__ . '/../includes/bootstrap.php'; // Loads Dotenv and autoload

session_start();

// 🔧 Define public pages as a constant
define('PUBLIC_PAGES', ['index.php', 'login.php', 'forgot-password.php', 'feedback-form.php', 'faqs.php']);

// 🔧 Define role-based access map
define('ROLE_ACCESS', [
    'admin'        => ['main-admin.php', 'admin-tools.php'],
    'staff'        => ['main-staff.php', 'staff-dashboard.php'],
    'super_admin'  => ['main-super-admin.php', 'super-settings.php'],
]);

// 🧠 Get current page name
$currentPage = basename($_SERVER['PHP_SELF']);

// ✅ Skip validation for public pages
if (in_array($currentPage, PUBLIC_PAGES)) {
    return;
}

// ✅ Validate login
if (!isset($_SESSION['username']) || !isset($_SESSION['role_name'])) {
>>>>>>> 6daf51bd0c038bd9f6b95409d26672fc23d288f9
    header('Location: /index.php');
    exit;
}

<<<<<<< HEAD
// ✅ Validate session token securely
$expectedToken = hash('sha256', $_ENV['SESSION_SECRET'] ?? '');
if ($_SESSION['user_token'] !== $expectedToken) {
=======
// ✅ Validate session token
if (!isset($_SESSION['user_token']) || $_SESSION['user_token'] !== hash('sha256', $_ENV['SESSION_SECRET'])) {
>>>>>>> 6daf51bd0c038bd9f6b95409d26672fc23d288f9
    header('Location: /login.php');
    exit;
}

<<<<<<< HEAD
// ✅ Enforce role-based access
$role = $_SESSION['role_name'];
$allowedPages = ROLE_ACCESS[$role] ?? [];

if (!in_array($currentPage, $allowedPages, true)) {
=======
// ✅ Role-based access control
$role = $_SESSION['role_name'];
if (isset(ROLE_ACCESS[$role]) && !in_array($currentPage, ROLE_ACCESS[$role])) {
>>>>>>> 6daf51bd0c038bd9f6b95409d26672fc23d288f9
    header('Location: /unauthorized.php'); // Create this page to show access denied
    exit;
}