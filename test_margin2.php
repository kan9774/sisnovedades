<?php
// Test v2 — más aislado todavía. Corré:
//   php test_margin2.php
// Genera test_margin2_output.pdf

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// SIN el reset "* { margin:0 }" — para descartar que interfiera.
// Margen en PULGADAS en vez de px, por si el parser de @page tiene
// algún problema puntual con px (hay reportes viejos de esto en dompdf).
$html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin-top: 1in;
        margin-right: 0.3in;
        margin-bottom: 0.5in;
        margin-left: 0.3in;
    }

    body { font-family: Arial, sans-serif; font-size: 12px; }
</style>
</head>
<body>
    <h1 style="background:yellow;">SI ESTO TOCA EL BORDE DE ARRIBA, @page NO FUNCIONA ACA.</h1>
    <p>Texto de prueba.</p>
</body>
</html>
HTML;

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultMediaType', 'print');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('a4', 'portrait');
$dompdf->render();

file_put_contents(__DIR__ . '/test_margin2_output.pdf', $dompdf->output());

echo "dompdf version: " . \Dompdf\Dompdf::VERSION . "\n";
echo "defaultMediaType: " . $options->getDefaultMediaType() . "\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Listo -> test_margin2_output.pdf\n";
