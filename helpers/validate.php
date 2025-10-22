<?php
// 🚦 Centralized input validation helpers

function sanitize($value) {
  return trim(htmlspecialchars($value));
}

function validateRequired($value, string $label): string {
  $value = sanitize($value);
  if (empty($value)) {
    throw new Exception("$label is required.");
  }
  return $value;
}

function validateLRN(PDO $pdo, string $lrn, ?int $excludeId = null): string {
  $lrn = sanitize($lrn);

  if (empty($lrn)) {
    throw new Exception('LRN is required.');
  }

  if (!preg_match('/^\d{12}$/', $lrn)) {
    throw new Exception('LRN must be exactly 12 digits.');
  }

  // 🧠 Check uniqueness, excluding current student if editing
  if ($excludeId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE lrn = ? AND id != ?");
    $stmt->execute([$lrn, $excludeId]);
  } else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
  }

  if ($stmt->fetchColumn() > 0) {
    throw new Exception('LRN already exists for another student.');
  }

  return $lrn;
}

function validateEmail(string $email, bool $required = false): ?string {
  $email = sanitize($email);
  if (empty($email)) {
    if ($required) throw new Exception('Email is required.');
    return null;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email format.');
  }

  return $email;
}

function validateContactNumber(string $number, bool $required = false): ?string {
  $number = sanitize($number);
  if (empty($number)) {
    if ($required) throw new Exception('Contact number is required.');
    return null;
  }

  if (!preg_match('/^\d{7,15}$/', $number)) {
    throw new Exception('Contact number must be 7–15 digits.');
  }

  return $number;
}

function validateDate(string $date, string $label = 'Date'): string {
  if (!strtotime($date)) {
    throw new Exception("Invalid $label.");
  }
  return $date;
}

function validateEnum(string $value, array $allowed, string $label): string {
  $value = sanitize($value);
  if (!in_array($value, $allowed)) {
    throw new Exception("Invalid $label. Allowed values: " . implode(', ', $allowed));
  }
  return $value;
}

function validateFileUpload(array $file, array $allowedTypes = ['image/jpeg', 'image/png'], int $maxSizeKB = 2048): ?string {
  if (empty($file['name'])) return null;

  if ($file['error'] !== UPLOAD_ERR_OK) {
    throw new Exception('File upload error.');
  }

  if (!in_array($file['type'], $allowedTypes)) {
    throw new Exception('Invalid file type. Allowed: JPG, PNG.');
  }

  if ($file['size'] > $maxSizeKB * 1024) {
    throw new Exception("File too large. Max size: {$maxSizeKB}KB.");
  }

  return $file['name'];
}