<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/fetch-feedback-data.php';

ob_start();
?>

<table class="min-w-full text-sm text-left">
  <thead class="bg-emerald-600 text-white">
    <tr>
      <?php
      function sortLink($label, $column)
      {
        $currentSort = $_GET['sort_by'] ?? '';
        $currentOrder = $_GET['sort_order'] ?? 'asc';
        $nextOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
        $arrow = ($currentSort === $column) ? ($currentOrder === 'asc' ? '▲' : '▼') : '⇅';

        return "<button class='sort-button cursor-pointer inline-flex items-center gap-1' data-column='$column' data-order='$nextOrder'>$label <span>$arrow</span></button>";
      }
      ?>
      <th data-column="id" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('ID', 'id') ?></th>
      <th data-column="name" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Name', 'name') ?></th>
      <th data-column="citizen_charter_awareness" class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= sortLink('Citizen Charter Awareness', 'citizen_charter_awareness') ?></th>
      <th data-column="cc1" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('CC1', 'cc1') ?></th>
      <th data-column="cc2" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('CC2', 'cc2') ?></th>
      <th data-column="cc3" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('CC3', 'cc3') ?></th>
      <th data-column="sqd1" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD1', 'sqd1') ?></th>
      <th data-column="sqd2" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD2', 'sqd2') ?></th>
      <th data-column="sqd3" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD3', 'sqd3') ?></th>
      <th data-column="sqd4" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD4', 'sqd4') ?></th>
      <th data-column="sqd5" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD5', 'sqd5') ?></th>
      <th data-column="sqd6" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD6', 'sqd6') ?></th>
      <th data-column="sqd7" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD7', 'sqd7') ?></th>
      <th data-column="sqd8" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('SQD8', 'sqd8') ?></th>
      <th data-column="remarks" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= sortLink('Remarks', 'remarks') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($results as $row): ?>
      <tr class="feedback-row border-y border-gray-300 hover:bg-emerald-50 transition-colors">
        <td data-column="id" class="px-4 py-2 text-left align-middle whitespace-nowrap text-red-500 font-medium transition-colors duration-300"><?= htmlspecialchars($row['id'] ?? '') ?></td>
        <td data-column="name" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['name'] ?? '') ?></td>
        <td data-column="citizen_charter_awareness" class="px-4 py-2 text-center align-middle whitespace-nowrap"><?= htmlspecialchars($row['citizen_charter_awareness'] ?? '') ?></td>
        <td data-column="cc1" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['cc1'] ?? '') ?></td>
        <td data-column="cc2" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['cc2'] ?? '') ?></td>
        <td data-column="cc3" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['cc3'] ?? '') ?></td>
        <td data-column="sqd1" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd1'] ?? '') ?></td>
        <td data-column="sqd2" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd2'] ?? '') ?></td>
        <td data-column="sqd3" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd3'] ?? '') ?></td>
        <td data-column="sqd4" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd4'] ?? '') ?></td>
        <td data-column="sqd5" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd5'] ?? '') ?></td>
        <td data-column="sqd6" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd6'] ?? '') ?></td>
        <td data-column="sqd7" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd7'] ?? '') ?></td>
        <td data-column="sqd8" class="px-4 py-2 text-left align-middle whitespace-nowrap transition-colors duration-300"><?= htmlspecialchars($row['sqd8'] ?? '') ?></td>
        <td data-column="remarks" class="px-4 py-2 transition-colors duration-300"><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo ob_get_clean(); ?>