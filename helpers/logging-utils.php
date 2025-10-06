<?php
function logFolderCreation(string $dbPath, string $diskPath): void {
  $logFile = __DIR__ . '/../logs/folder_creation.log';
  $timestamp = date('Y-m-d H:i:s');
  $message = "[$timestamp] Folder created → DB: $dbPath | Disk: $diskPath\n";
  error_log($message, 3, $logFile);
}

function logRenameAction(string $type, string $oldPath, string $newPath): void {
  $logFile = __DIR__ . '/../logs/rename_actions.log';
  $timestamp = date('Y-m-d H:i:s');
  $message = "[$timestamp] Renamed $type → FROM: $oldPath TO: $newPath\n";
  error_log($message, 3, $logFile);
}

function logSuggestionFetch(string $query, string $excludeEmail, string $context = 'JS'): void {
  $logFile   = __DIR__ . '/../logs/suggestion_fetch.log';
  $timestamp = date('Y-m-d H:i:s');
  $message   = "[$timestamp] [$context] Suggestion fetch → query: \"$query\" exclude: \"$excludeEmail\"\n";
  error_log($message, 3, $logFile);
}