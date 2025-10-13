<?php if (empty($results)): ?>
  <div class="text-center py-10 text-gray-500 italic flex flex-col items-center gap-2">
    <img src="/assets/img/empty-box.png" class="w-10 h-10 opacity-50" alt="Empty">
    No feedback respondents found at the moment.
  </div>
<?php else: ?>
  <table class="min-w-full text-sm text-left">
    <thead class="bg-emerald-600 text-white">
      <tr>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">ID</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">Name</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">Citizen Charter Awareness</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">CC1</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">CC2</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">CC3</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD1</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD2</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD3</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD4</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD5</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD6</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD7</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">SQD8</th>
        <th class="px-4 py-2 text-left align-middle whitespace-nowrap">Remarks</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $row): ?>
        <tr class="feedback-row border-y border-gray-300 hover:bg-emerald-50 transition-colors">
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap text-red-500 font-medium"><?= htmlspecialchars($row['id'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['name'] ?? '') ?></td>
          <td class="px-4 py-2 text-center align-middle whitespace-nowrap"><?= htmlspecialchars($row['citizen_charter_awareness'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['cc1'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['cc2'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['cc3'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd1'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd2'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd3'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd4'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd5'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd6'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd7'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle whitespace-nowrap"><?= htmlspecialchars($row['sqd8'] ?? '') ?></td>
          <td class="px-4 py-2 text-left align-middle"><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>