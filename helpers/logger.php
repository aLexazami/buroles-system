<?php
function logDebug(string $message, string $context = 'shared-file'): void {
  $logDir = __DIR__ . '/../logs';
  if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
  }

  $filename = $logDir . "/{$context}.log";
  $timestamp = date('Y-m-d H:i:s');
  $entry = "[{$timestamp}] {$message}\n";

  file_put_contents($filename, $entry, FILE_APPEND);
}

function logFolderEvent(string $context, array $details = [], bool $isError = false): void {
  $timestamp = date('Y-m-d H:i:s');
  $userId = $details['userId'] ?? 'unknown';
  $scopedPath = $details['scopedPath'] ?? 'N/A';

  $logDir = __DIR__ . '/../logs/folders';
  if (!is_dir($logDir)) mkdir($logDir, 0755, true);

  $logFile = "$logDir/user_$userId.log";
  $prefix = $isError ? '❌ Folder creation error' : '✅ Folder creation success';
  $message = "[$timestamp] $prefix → $context | scopedPath=$scopedPath";

  unset($details['userId'], $details['scopedPath']);
  if (!empty($details)) {
    $message .= ' | ' . json_encode($details, JSON_UNESCAPED_SLASHES);
  }

  error_log($message . "\n", 3, $logFile);
}

function logDeletionFolderEvent(string $context, array $details = [], bool $isError = false): void {
  $timestamp = date('Y-m-d H:i:s');
  $userId = $details['userId'] ?? 'unknown';
  $scopedPath = $details['scopedPath'] ?? 'N/A';

  $logDir = __DIR__ . '/../logs/folders';
  if (!is_dir($logDir)) mkdir($logDir, 0755, true);

  $logFile = "$logDir/user_$userId.log";
  $prefix = $isError ? '❌ Deletion error' : '✅ Deletion success';
  $message = "[$timestamp] $prefix → $context | scopedPath=$scopedPath";

  unset($details['userId'], $details['scopedPath']);
  if (!empty($details)) {
    $message .= ' | ' . json_encode($details, JSON_UNESCAPED_SLASHES);
  }

  error_log($message . "\n", 3, $logFile);
}

function logRenameEvent(string $message, array $context = [], bool $isError = false): void {
  $logDir = __DIR__ . '/../logs';
  if (!is_dir($logDir)) mkdir($logDir, 0775, true);

  $logFile = $logDir . '/rename.log';
  $timestamp = date('[Y-m-d H:i:s]');
  $status = $isError ? '❌ ERROR' : '✅ SUCCESS';
  $contextJson = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

  file_put_contents($logFile, "$timestamp $status → $message | $contextJson\n", FILE_APPEND);
}