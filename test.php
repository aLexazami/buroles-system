<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Options;
use Dompdf\Dompdf;

$options = new Options();
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml('<h1>Hello PDF</h1>');
$dompdf->render();
$dompdf->stream('test.pdf');