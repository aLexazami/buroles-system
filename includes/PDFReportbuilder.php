<?php
use Dompdf\Dompdf;
use Dompdf\Options;

class PDFReportBuilder {
  private $dompdf;

  public function __construct() {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $this->dompdf = new Dompdf($options);
  }

  public function build($title, $tableHtml, $styles = '') {
    $html = "<style>$styles</style>";
    $html .= "<h2 style='text-align:center;'>$title</h2>";
    $html .= $tableHtml;

    $this->dompdf->loadHtml($html);
    $this->dompdf->setPaper('A4', 'landscape');
    $this->dompdf->render();
  }

  public function stream($filename = 'report.pdf', $download = true) {
    $this->dompdf->stream($filename, ['Attachment' => $download]);
  }
}
?>