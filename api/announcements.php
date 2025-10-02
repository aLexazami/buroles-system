<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$currentRoleId = $_SESSION['user']['role_id'] ?? null;
$userId = $_SESSION['user']['id'] ?? null;

$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

try {
  if ((int) $currentRoleId === 99) {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM announcements");
    $totalAnnouncements = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT a.id, a.title, a.body, a.target_role_id, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      ORDER BY a.created_at DESC
      LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
  } else {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM announcements WHERE target_role_id IN (?, 100)");
    $countStmt->execute([$currentRoleId]);
    $totalAnnouncements = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
      SELECT a.id, a.title, a.body, a.target_role_id, a.created_at, u.username AS author
      FROM announcements a
      JOIN users u ON a.created_by = u.id
      WHERE a.target_role_id IN (?, 100)
      ORDER BY a.created_at DESC
      LIMIT ? OFFSET ?
    ");
    $stmt->execute([$currentRoleId, $limit, $offset]);
  }

  $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $totalPages = ceil($totalAnnouncements / $limit);

  foreach ($announcements as &$note) {
    $readStmt = $pdo->prepare("SELECT 1 FROM announcement_reads WHERE user_id = ? AND announcement_id = ?");
    $readStmt->execute([$userId, $note['id']]);
    $alreadyRead = $readStmt->fetchColumn();

    $note['is_new'] = !$alreadyRead && strtotime($note['created_at']) >= strtotime('-1 days');

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