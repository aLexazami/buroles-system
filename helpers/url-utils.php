<?php
function getBaseUrl(): string {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return $scheme . '://' . $host;
}

function generateResetLink(string $token): string {
  return getBaseUrl() . '/pages/new-password.php?token=' . urlencode($token);
}