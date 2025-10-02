<?php include __DIR__ . '/dashboard-data.php';?>
<div class="bg-gray-300  h-full">
  <h2 class="bg-emerald-600 py-2 text-white text-center font-bold ">Announcements</h2>
  <div class="p-2 space-y-2">
    <?php if (!empty($announcements)): ?>
      <?php foreach ($announcements as $note): ?>
  <?php
  $stmt = $pdo->prepare("SELECT 1 FROM announcement_reads WHERE user_id = ? AND announcement_id = ?");
$stmt->execute([$_SESSION['user_id'], $note['id']]);
$alreadyRead = $stmt->fetchColumn();
    $isNew = !$alreadyRead && strtotime($note['created_at']) >= strtotime('-1 days');
    $readKey = 'announcement_read_' . $note['id'];
  ?>
  <?php
  $createdAt = new DateTime($note['created_at']);
  $now = new DateTime();
  $interval = $createdAt->diff($now);

  if ($interval->y >= 1) {
    $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
  } elseif ($interval->m >= 1) {
    $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
  } elseif ($interval->d >= 7) {
    $weeks = floor($interval->d / 7);
    $timeAgo = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
  } elseif ($interval->d >= 1) {
    $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
  } else {
    $timeAgo = 'Today';
  }
?>
  <div
    class="relative cursor-pointer p-5 border-l-4 border-emerald-600 transition <?= $isNew ? 'bg-emerald-50 hover:bg-emerald-100' : 'bg-white hover:bg-gray-100' ?>"
    data-viewer-trigger
    data-id="<?= $note['id'] ?>"
    data-title="<?= htmlspecialchars($note['title']) ?>"
    data-body="<?= htmlspecialchars(sentenceCase($note['body'])) ?>"
    data-role="<?= $roleMap[$note['target_role_id']] ?? 'For All' ?>"
    data-date="<?= date('F j, Y', strtotime($note['created_at'])) ?>"
  >
    <?php if ($isNew): ?>
    <span class="new-badge absolute top-2 left-1 text-[8px] bg-green-600 text-white px-2 py-1 rounded-full z-10">New</span>
  <?php endif; ?>


    <h3 class="font-semibold text-gray-800"><?= ucwords(htmlspecialchars($note['title'])) ?></h3>
    <p class="text-xs text-gray-500 italic"><?= $timeAgo ?></p>
  </div>
<?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-gray-500 italic">No announcements available.</p>
    <?php endif; ?>
  </div>
</div>

<!-- 📖 Dynamic Announcement Viewer -->
<?php require __DIR__ . '/../components/announcement-viewer.php'; ?>
