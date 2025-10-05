<?php
function redirectTo(string $url): void {
  header("Location: $url");
  exit();
}

function mustChangePassword(PDO $pdo, int $userId): bool {
  $stmt = $pdo->prepare("SELECT must_change_password FROM users WHERE id = ?");
  $stmt->execute([$userId]);
  return (bool) $stmt->fetchColumn();
}

function updatePassword(PDO $pdo, int $userId, string $newPassword): bool {
  $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
  return $stmt->execute([$hashed, $userId]);
}

function getUserRole(PDO $pdo, int $userId): int {
  $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
  $stmt->execute([$userId]);
  return (int) $stmt->fetchColumn();
}

function getUserByEmail(PDO $pdo, string $email): ?array {
  $stmt = $pdo->prepare("SELECT id, email, role_id FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  return $user ?: null;
}