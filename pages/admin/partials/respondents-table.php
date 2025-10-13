<?php
$markRead = $pdo->prepare("UPDATE feedback_respondents SET is_read = TRUE WHERE is_read = FALSE");
$markRead->execute();
?>

<?php if (empty($results)): ?>
  <div class="text-center py-10 text-gray-500 italic flex flex-col items-center gap-2">
    <img src="/assets/img/empty-box.png" class="w-10 h-10 opacity-50" alt="Empty">
    No feedback respondents found at the moment.
  </div>
<?php else: ?>
  <table class="min-w-full text-sm text-left">
    <thead class="bg-emerald-600 text-white">
      <tr>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">ID</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">Name</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">Date</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">Age</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">Sex</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">Customer Type</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">Service Availed</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300">Region</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $row): ?>
        <tr class="respondent-row border-y border-gray-300 <?= !$row['is_read'] ? 'bg-emerald-100 hover:bg-emerald-200' : 'hover:bg-emerald-50' ?> transition-colors">
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap text-red-500 font-medium transition-colors duration-300">
            <?= htmlspecialchars($row['id'] ?? '') ?>
            <?php if (!$row['is_read']): ?>
              <span class="bg-green-600 rounded-full py-1 px-2 text-white text-[10px]" title="Unread">New</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['name'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['date'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['age'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sex'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['customer_type'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle transition-colors duration-300"><?= htmlspecialchars($row['service_availed'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle transition-colors duration-300"><?= htmlspecialchars($row['region'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>