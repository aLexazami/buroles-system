<?php

/**
 * Render a single shared item (file or folder) with appropriate icons and actions.
 */
function renderSharedTree(array $tree, int $depth = 0): void
{
  echo '<ul class="ml-' . ($depth * 4) . ' space-y-1">';

  foreach ($tree as $name => $node) {
    $item     = $node['__meta'] ?? null;
    $children = $node['__children'] ?? [];

    echo '<li>';

    if ($item && isset($item['name'], $item['path'], $item['type'])) {
      $icon = ($item['type'] ?? '') === 'file' ? '📄' : '📁';

      echo '<div class="flex flex-wrap px-2 py-2 items-center w-full text-sm text-gray-700">';
      echo '  <div class="flex items-center gap-2 sm:gap-3 flex-grow">';
      echo '    <span>' . $icon . '</span>';
      echo '    <span class="font-medium">' . htmlspecialchars($item['name']) . '</span>';
      echo '  </div>';
      echo '  <div class="hidden sm:flex items-center text-center gap-2 sm:gap-3">';
      echo '    <span class="w-24 text-xs sm:text-sm">' . htmlspecialchars($item['shared_by_name'] ?? 'Unknown') . '</span>';
      echo '    <span class="w-32 text-xs sm:text-sm">' . htmlspecialchars(date('M d, Y', strtotime($item['shared_at'] ?? ''))) . '</span>';
      echo '  </div>';
      echo '  <div class="w-10"></div>';
      echo '</div>';
    } elseif ($depth === 0) {
      renderOrphanedNode($name);
    }

    if (!empty($children) && $depth === 0) {
      // Only render children at root level if needed
      renderSharedTree($children, $depth + 1);
    }

    echo '</li>';
  }

  echo '</ul>';
}
