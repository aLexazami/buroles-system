<?php

/**
 * Get icon filename for a given file extension.
 */
function getFileIcon(string $filename): string {
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $iconMap = [
    'pdf'   => 'pdf.png',
    'doc'   => 'doc.png',
    'docx'  => 'doc.png',
    'jpg'   => 'image.png',
    'jpeg'  => 'image.png',
    'png'   => 'image.png',
    'gif'   => 'image.png',
    'zip'   => 'zip.png',
    'rar'   => 'zip.png',
  ];

  return "/assets/img/icons/" . ($ext ? ($iconMap[$ext] ?? 'file.png') : 'file.png');
}

/**
 * Count all files inside a directory recursively.
 */
function countUserFiles(string $basePath): int {
  if (!is_dir($basePath)) return 0;

  $count = 0;
  $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));

  foreach ($rii as $file) {
    if ($file->isFile()) $count++;
  }

  return $count;
}
?>