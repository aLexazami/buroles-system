<?php
//  Session and DB setup
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

//  Get current user role
$currentRoleId = $_SESSION['user']['role_id'] ?? null;

//  Announcement fetch logic
try {
  if ((int) $currentRoleId === 99) {
    //  Super Admin sees all announcements
    $stmt = $pdo->query("
      SELECT a.id, a.title, a.body, a.target_role_id, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      ORDER BY a.created_at DESC
      LIMIT 20
    ");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    //  Other roles see announcements for their role + 'For All' (role_id = 100)
    $stmt = $pdo->prepare("
      SELECT a.id, a.title, a.body, a.target_role_id, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      WHERE a.target_role_id IN (:role_id, 100)
      ORDER BY a.created_at DESC
      LIMIT 20
    ");
    $stmt->execute([':role_id' => $currentRoleId]);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

} catch (PDOException $e) {
  error_log('Failed to fetch announcements: ' . $e->getMessage());
  $announcements = [];
}