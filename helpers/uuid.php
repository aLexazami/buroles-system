<?php
function generateUuid(): string {
  // Generate 16 random bytes
  $data = random_bytes(16);

  // Set version to 0100 (UUID v4)
  $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);

  // Set variant to 10xx
  $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

  // Format as UUID string
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function isValidUuid($uuid) {
  if (!is_string($uuid) || strlen($uuid) !== 36) return false;
  return preg_match('/^[a-f0-9\-]{36}$/i', $uuid) === 1;
}