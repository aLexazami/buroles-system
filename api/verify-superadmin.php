<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$password = $input['password'] ?? '';
$userId = (int)($input['user_id'] ?? 0);

$isSuperAdmin = ($_SESSION['role_id'] ?? 0) === 2;

if (!$isSuperAdmin) {
  echo json_encode(['success' => false]);
  exit;
}

$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$storedHash = $stmt->fetchColumn();

if (password_verify($password, $storedHash)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false]);
}