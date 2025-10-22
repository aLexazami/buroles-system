<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
  $stmt = $pdo->query("
    SELECT
      id,
      lrn,
      CONCAT_WS(' ', first_name, middle_name, last_name) AS full_name,
      gender,
      photo_path
    FROM students
    ORDER BY last_name ASC, first_name ASC
  ");

  $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'students' => $students
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'error' => 'Failed to fetch students.',
    'details' => $e->getMessage()
  ]);
}