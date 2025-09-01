<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$newCount = getRespondentCount('new', $pdo);
$weeklyCount = getRespondentCount('weekly', $pdo);
$annualCount = getRespondentCount('annual', $pdo);
?>