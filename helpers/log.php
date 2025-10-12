<?php
function logAction(PDO $pdo, int $userId, string $fileId, string $fileName, string $action, string $detailsJson): void {
  $stmt = $pdo->prepare("
    INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
    VALUES (UUID(), ?, ?, ?, ?, ?, 'dashboard')
  ");
  $stmt->execute([$fileId, $fileName, $userId, $action, $detailsJson]);
}