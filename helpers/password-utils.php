<?php
function getResetRecord($pdo, $token) {
  $stmt = $pdo->prepare("SELECT user_id, expires_at, used FROM password_resets WHERE token = ?");
  $stmt->execute([$token]);
  return $stmt->fetch();
}

function markTokenUsed($pdo, $token) {
  $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
  $stmt->execute([$token]);
}

function getPasswordErrors($password) {
  $errors = [];

  if (strlen($password) < 8) {
    $errors[] = 'at least 8 characters';
  }
  if (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'an uppercase letter';
  }
  if (!preg_match('/[0-9]/', $password)) {
    $errors[] = 'a number';
  }
  if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    $errors[] = 'a special character (e.g. !@#$%)';
  }

  return $errors;
}

function formatPasswordErrors(array $errors) {
  if (count($errors) > 1) {
    $last = array_pop($errors);
    return implode(', ', $errors) . ' and ' . $last;
  }
  return implode('', $errors);
}