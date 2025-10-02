<?php
// Session and DB setup
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

// Get current user role
$currentRoleId = $_SESSION['user']['role_id'] ?? null;

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
  // Count total announcements for pagination
  if ((int) $currentRoleId === 99) {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM announcements");
    $totalAnnouncements = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT a.id, a.title, a.body, a.target_role_id, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      ORDER BY a.created_at DESC
      LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM announcements WHERE target_role_id IN (:role_id, 100)");
    $countStmt->execute([':role_id' => $currentRoleId]);
    $totalAnnouncements = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT a.id, a.title, a.body, a.target_role_id, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      WHERE a.target_role_id IN (:role_id, 100)
      ORDER BY a.created_at DESC
      LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':role_id', $currentRoleId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  $totalPages = ceil($totalAnnouncements / $limit);

} catch (PDOException $e) {
  error_log('Failed to fetch announcements: ' . $e->getMessage());
  $announcements = [];
  $totalPages = 1;
}