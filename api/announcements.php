<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

$currentRoleId = $_SESSION['user']['role_id'] ?? null;
$userId = $_SESSION['user']['id'] ?? null;

$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
  // Count total announcements
  if ((int) $currentRoleId === 99) {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM announcements");
    $totalAnnouncements = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT a.id, a.title, a.body, a.target_role_id, r.name AS role_name, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      LEFT JOIN roles r ON a.target_role_id = r.id
      ORDER BY a.created_at DESC
      LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
  } else {
    $countStmt = $pdo->prepare("
      SELECT COUNT(*) FROM announcements
      WHERE target_role_id = :role_id OR target_role_id = 100
    ");
    $countStmt->execute([':role_id' => $currentRoleId]);
    $totalAnnouncements = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT a.id, a.title, a.body, a.target_role_id, r.name AS role_name, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      LEFT JOIN roles r ON a.target_role_id = r.id
      WHERE a.target_role_id = :role_id OR a.target_role_id = 100
      ORDER BY a.created_at DESC
      LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':role_id', $currentRoleId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
  }

  $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $totalPages = ceil($totalAnnouncements / $limit);

  foreach ($announcements as &$note) {
    // Check if user has read this announcement
    $readStmt = $pdo->prepare("
      SELECT 1 FROM announcement_reads
      WHERE user_id = ? AND announcement_id = ?
    ");
    $readStmt->execute([$userId, $note['id']]);
    $note['already_read'] = (bool) $readStmt->fetchColumn();

    // Format time ago
    $createdAt = new DateTime($note['created_at']);
    $now = new DateTime();
    $interval = $createdAt->diff($now);

    if ($interval->y >= 1) {
      $note['time_ago'] = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    } elseif ($interval->m >= 1) {
      $note['time_ago'] = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    } elseif ($interval->d >= 7) {
      $weeks = floor($interval->d / 7);
      $note['time_ago'] = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($interval->d >= 1) {
      $note['time_ago'] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    } else {
      $note['time_ago'] = 'Today';
    }
  }

  echo json_encode([
    'announcements' => $announcements,
    'pagination' => [
      'current_page' => $page,
      'total_pages' => $totalPages
    ]
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Database error',
    'details' => $e->getMessage()
  ]);
}