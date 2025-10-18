<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

$teacherId = $_SESSION['user_id'];

$query = "SELECT s.id, s.first_name, s.last_name
          FROM students s
          JOIN classes c ON s.class_id = c.id
          WHERE c.adviser_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$teacherId]);
$students = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($students);