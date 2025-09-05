<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../includes/fetch-feedback-data.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/PDFReportBuilder.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ✅ Define fields
$fields = [
  'id', 'name', 'date', 'age', 'sex', 'customer_type',
  'service_availed', 'region', 'submitted_at',
  'citizen_charter_awareness', 'cc1', 'cc2', 'cc3',
  'sqd1', 'sqd2', 'sqd3', 'sqd4', 'sqd5', 'sqd6', 'sqd7', 'sqd8',
  'remarks'
];

// ✅ Build table HTML
$table = '<table><thead><tr>';
foreach ($fields as $field) {
  $label = ucwords(str_replace('_', ' ', $field));
  $table .= "<th>$label</th>";
}
$table .= '</tr></thead><tbody>';

foreach ($results as $row) {
  $table .= '<tr>';
  foreach ($fields as $field) {
    $value = htmlspecialchars($row[$field] ?? '');
    if ($field === 'remarks') {
      $value = wordwrap($value, 100, ' ', true);
    }
    $table .= "<td>$value</td>";
  }
  $table .= '</tr>';
}
$table .= '</tbody></table>';

// ✅ Define styles
$styles = "
  body { font-family: Arial, sans-serif; }
  table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
  th, td { border: 1px solid #ccc; padding: 4px; text-align: left; word-wrap: break-word; vertical-align: top; }
  th { background-color: #f0f0f0; }
  td:last-child { width: 200px; }
";

// ✅ Build and stream PDF
$builder = new PDFReportBuilder();
$builder->build('Burol Elementary Feedback Report', $table, $styles);
$builder->stream('feedback_report.pdf');
exit;
?>