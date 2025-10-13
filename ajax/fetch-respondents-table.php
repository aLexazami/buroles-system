<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/fetch-feedback-data.php';

$searchTerm = $_GET['search'] ?? '';
$sortKey = $_GET['sort_by'] ?? 'id';
$sortOrder = $_GET['sort_order'] ?? 'desc';
$results = getFeedbackData($pdo, $sortKey, $sortOrder, $searchTerm);

include __DIR__ . '/../pages/admin/partials/respondents-table.php';