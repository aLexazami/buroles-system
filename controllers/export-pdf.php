<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/get-feedback-data-core.php';

$serviceId = $_GET['service_id'] ?? null;
$stmt = $pdo->prepare("SELECT name FROM services WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $serviceId]);
$serviceName = $stmt->fetchColumn();

if (!$serviceName) {
  http_response_code(404);
  echo "Service not found";
  exit;
}

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

if (!$serviceId || !$from || !$to) {
  http_response_code(400);
  echo "Missing parameters";
  exit;
}

// ✅ Fetch full report data
$reportData = getFeedbackData($pdo, $serviceId, $from, $to);

if (!$reportData || !is_array($reportData)) {
  http_response_code(500);
  echo "Failed to retrieve report data";
  exit;
}

// ✅ Build output filename
$outputFile = "feedback-report-$serviceId-$from-$to.pdf";

// ✅ Resolve paths
$nodeScript = realpath(__DIR__ . '/../puppeteer-export/export-report.js');
$exportDir = realpath(__DIR__ . '/../exports');
$pdfPath = $exportDir . DIRECTORY_SEPARATOR . $outputFile;

if (!$nodeScript || !$exportDir) {
  http_response_code(500);
  echo "Path resolution failed";
  exit;
}

// ✅ Write JSON to temp file
$tempJsonPath = tempnam(sys_get_temp_dir(), 'report_') . '.json';
file_put_contents($tempJsonPath, json_encode($reportData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

// ✅ Build and run command
$escapedService = escapeshellarg($serviceId);
$escapedFrom = escapeshellarg($from);
$escapedTo = escapeshellarg($to);
$escapedOutputFile = escapeshellarg($outputFile);
$escapedJsonPath = escapeshellarg($tempJsonPath);
$escapedServiceName = escapeshellarg($serviceName);

$command = "node $nodeScript $escapedServiceName $escapedFrom $escapedTo $escapedOutputFile $escapedJsonPath";
exec($command . ' 2>&1', $output, $status);
file_put_contents(__DIR__ . '/../log/export-output.log', implode("\n", $output));

// ✅ Serve the PDF
if (file_exists($pdfPath)) {
  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="' . basename($pdfPath) . '"');
  readfile($pdfPath);
  unlink($tempJsonPath); // ✅ Clean up temp file
  exit;
} else {
  http_response_code(500);
  echo "PDF generation failed";
}