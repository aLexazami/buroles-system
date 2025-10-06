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
      <?php
      function sortLink($label, $column) {
        $currentSort = $_GET['sort_by'] ?? '';
        $currentOrder = $_GET['sort_order'] ?? 'asc';
        $nextOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
        $arrow = ($currentSort === $column) ? ($currentOrder === 'asc' ? '▲' : '▼') : '⇅';
        return "<button class='sort-button cursor-pointer inline-flex items-center gap-1' data-column='$column' data-order='$nextOrder'>$label <span>$arrow</span></button>";
      }
      ?>
      <th data-column="id" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('ID', 'id') ?></th>
      <th data-column="name" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Name', 'name') ?></th>
      <th data-column="date" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Date', 'date') ?></th>
      <th data-column="age" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Age', 'age') ?></th>
      <th data-column="sex" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Sex', 'sex') ?></th>
      <th data-column="customer_type" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Customer Type', 'customer_type') ?></th>
      <th data-column="service_availed" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Service Availed', 'service_availed') ?></th>
      <th data-column="region" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Region', 'region') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($results as $row): ?>
      <tr class="respondent-row border-y border-gray-300 <?= !$row['is_read'] ? 'bg-emerald-100 hover:bg-emerald-200' : 'hover:bg-emerald-50' ?> transition-colors">
        <td data-column="id" class="px-4 py-2 text-left align-middle whitespace-nowrap text-red-500 font-medium transition-colors duration-300"><?= htmlspecialchars($row['id'] ?? '') ?>
          <?php if (!$row['is_read']): ?>
            <span class="bg-green-600 rounded-full py-1 px-2 text-white text-[10px]" title="Unread">New</span>
          <?php endif; ?>
        </td>
        <td data-column="name" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['name'] ?? '') ?></td>
        <td data-column="date" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['date'] ?? '') ?></td>
        <td data-column="age" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['age'] ?? '') ?></td>
        <td data-column="sex" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sex'] ?? '') ?></td>
        <td data-column="customer_type" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['customer_type'] ?? '') ?></td>
        <td data-column="service_availed" class="px-4 py-2 text-left align-middle  transition-colors duration-300"><?= htmlspecialchars($row['service_availed'] ?? '') ?></td>
        <td data-column="region" class="px-4 py-2 text-left align-middle transition-colors duration-300"><?= htmlspecialchars($row['region'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>