<?php

namespace Tests\Support;

/**
 * Genera archivos PDF dummy de tamaño exacto para pruebas de estrés.
 *
 * El PDF tiene estructura mínima válida (header, objects, xref, trailer, EOF)
 * y el resto del espacio se llena con contenido dummy repetitivo.
 * NO es un PDF legítimo — solo sirve para probar límites de memoria y tamaño.
 */
class DummyPdfGenerator
{
    /**
     * Genera un string binario de ~$tamanoBytes con estructura PDF mínima.
     */
    public static function generar(int $tamanoBytes): string
    {
        // Header PDF
        $pdf = "%PDF-1.4\n";

        // Catalog object
        $catalog = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= $catalog;

        // Pages object
        $pages = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= $pages;

        // Page object
        $page = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\n";
        $pdf .= $page;

        // Stream object with content
        $streamHeader = "4 0 obj\n<< /Length " . ($tamanoBytes - strlen($pdf) - 100) . " >>\nstream\n";
        $streamFooter = "\nendstream\nendobj\n";
        $pdf .= $streamHeader;

        // Fill with dummy content (42 chars per line for alignment)
        $content = str_repeat("DUMMY_CONTENT_BLOCK_0123456789ABCDEF0123456789ABCDEF\n", 1);
        $needed = max(0, $tamanoBytes - strlen($pdf) - strlen($streamFooter) - 50);
        $repeats = (int) ceil($needed / strlen($content));
        $pdf .= str_repeat($content, $repeats);

        $pdf .= $streamFooter;

        // xref and trailer
        $xref = "xref\n0 5\n0000000000 65535 f \n";
        // Calculate approximate offsets (simplified)
        $offset = 0;
        for ($i = 0; $i < 4; $i++) {
            $xref .= str_pad((string) $offset, 10, "0", STR_PAD_LEFT) . " 00000 n \n";
            $offset += strlen($catalog) + strlen($pages) + strlen($page) + ($i === 3 ? strlen($streamHeader) : 0);
        }
        $xref .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n" . strlen($pdf) . "\n%%EOF\n";
        $pdf .= $xref;

        // Trim or pad to exact size
        $currentSize = strlen($pdf);
        if ($currentSize < $tamanoBytes) {
            $pdf .= str_repeat("X", $tamanoBytes - $currentSize);
        } else {
            $pdf = substr($pdf, 0, $tamanoBytes);
        }

        return $pdf;
    }

    /**
     * Genera un string dummy de $tamanoBytes sin estructura PDF.
     * Útil para pruebas donde solo importa el tamaño, no el formato.
     */
    public static function generarBinario(int $tamanoBytes): string
    {
        $chunk = str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz\n", 1);
        $repeats = (int) ceil($tamanoBytes / strlen($chunk));
        $result = str_repeat($chunk, $repeats);

        return substr($result, 0, $tamanoBytes);
    }
}
