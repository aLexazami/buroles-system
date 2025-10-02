<?php
require_once __DIR__ . '/../includes/bootstrap.php'; // Loads Dotenv and Composer autoload
require_once __DIR__ . '/../helpers/flash.php';      // Optional: for messaging

// 🔓 Public pages that require no authentication
$publicPages = [
    'index.php',
    'login.php',
    'reset-password.php',
    'feedback-form.php',
    'faqs.php',
    '404.php',
    '500.php'
];

$currentPage = basename($_SERVER['PHP_SELF']);

// ✅ Allow public pages
if (in_array($currentPage, $publicPages, true)) {
    return;
}

// ✅ Validate session essentials
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['username']) ||
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
