<?php
require_once __DIR__ . '/../includes/bootstrap.php'; // Loads Dotenv and Composer autoload
require_once __DIR__ . '/../helpers/flash.php';      // Optional: for messaging

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔓 Public pages that require no authentication
$publicPages = [
    'index.php',
    'login.php',
    'reset-password.php',
    'feedback-form.php',
    'new-password.php',
    'faqs.php',
    '404.php',
    '500.php',
    'client-error.php',        // ✅ Add this
  'feature-usage.php',
];

$currentPage = basename($_SERVER['PHP_SELF']);

// ✅ Allow public pages
if (in_array($currentPage, $publicPages, true)) {
    return;
}

// ✅ Validate session essentials
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['username']) ||
    empty($_SESSION['user_token'])
) {
    if ($isAjax) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    } else {
        header('Location: /index.php');
    }
    exit;
}

// ✅ Validate session token securely
$expectedToken = hash('sha256', $_ENV['SESSION_SECRET'] ?? '');
if ($_SESSION['user_token'] !== $expectedToken) {
    header('Location: /login.php');
    exit;
}
