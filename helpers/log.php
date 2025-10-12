<?php
function logAction(PDO $pdo, int $userId, string $fileId, string $action, string $details): void {
  $stmt = $pdo->prepare("
    INSERT INTO logs (id, file_id, user_id, action, details, source)
    VALUES (UUID(), ?, ?, ?, ?, 'dashboard')
  ");
  $stmt->execute([$fileId, $userId, $action, $details]);
}