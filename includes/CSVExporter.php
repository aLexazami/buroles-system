<?php

class CSVExporter {
  private $fields;
  private $filename;
  private $includeBom;

  public function __construct(array $fields, string $filename = 'export.csv', bool $includeBom = true) {
    $this->fields = $fields;
    $this->filename = $filename;
    $this->includeBom = $includeBom;
  }

  public function stream(array $data) {
    // ✅ Set headers
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$this->filename}\"");

    // ✅ Optional BOM for Excel
    if ($this->includeBom) {
      echo "\xEF\xBB\xBF";
    }

    $output = fopen('php://output', 'w');

    // ✅ Write column headers
    $headers = array_map(function($field) {
      return ucwords(str_replace('_', ' ', $field));
    }, $this->fields);
    fputcsv($output, $headers);

    // ✅ Write rows
    foreach ($data as $row) {
      $rowData = [];
      foreach ($this->fields as $field) {
        $value = $row[$field] ?? '';
        $value = str_replace(["\r", "\n"], ' ', $value); // sanitize line breaks
        $rowData[] = $value;
      }
      fputcsv($output, $rowData);
    }

    fclose($output);
    exit;
  }
}
?>