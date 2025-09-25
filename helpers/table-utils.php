<?php
function sortLink($label, $column) {
  $currentSort = $_GET['sort_by'] ?? '';
  $currentOrder = $_GET['sort_order'] ?? 'asc';
  $nextOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';

  $arrow = ($currentSort === $column)
    ? ($currentOrder === 'asc' ? '▲' : '▼')
    : '⇅';

  $url = "?sort_by=$column&sort_order=$nextOrder";
  return "<a href=\"$url\" class=\"inline-flex items-center gap-1\">$label <span>$arrow</span></a>";
}
