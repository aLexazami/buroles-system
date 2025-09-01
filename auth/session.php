<?php
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
    header('Location: /index.php');
    exit;
}

// ✅ Validate session token securely
$expectedToken = hash('sha256', $_ENV['SESSION_SECRET'] ?? '');
if ($_SESSION['user_token'] !== $expectedToken) {
    header('Location: /login.php');
    exit;
}

// ✅ Enforce role-based access
$role = $_SESSION['role_name'];
$allowedPages = ROLE_ACCESS[$role] ?? [];

if (!in_array($currentPage, $allowedPages, true)) {
    header('Location: /unauthorized.php'); // Create this page to show access denied
    exit;
}