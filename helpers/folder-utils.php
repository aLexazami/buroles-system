<?php
function listFolderItems(string $path): array {
  if (!is_dir($path)) return ['folders' => [], 'files' => []];

  $items = scandir($path);
  return [
    'folders' => array_filter($items, fn($item) => is_dir($path . '/' . $item) && $item !== '.' && $item !== '..'),
    'files' => array_filter($items, fn($item) => is_file($path . '/' . $item))
  ];
}

function createFolder($basePath, $folderName): bool {
  $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folderName);
  $target = $basePath . '/' . $safeName;
  if (!file_exists($target)) {
    return mkdir($target, 0755, true);
  }
  return false;
}
?>