<?php

namespace App\Support;

use App\Models\Guard;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfContract;

class RecibidosPdfGenerator
{
    /**
     * Genera PDF de recibidos con archivos incrustados
     *
     * @param Guard $guardia
     * @return PdfContract
     */
    public static function generar(Guard $guardia): PdfContract
    {
        $guardia->loadMissing([
            'capitan',
            'oficial',
            'escribiente',
            'novedades.organismo',
            'novedades.adjuntos',
        ]);

        return Pdf::loadView('admin.guardias.pdf.recibidos.recibidos-con-archivos', ['guardia' => $guardia])
            ->setPaper('a4', 'portrait');
    }

    /**
     * Genera nombre del archivo PDF
     *
     * @param Guard $guardia
     * @return string
     */
    public static function nombreArchivo(Guard $guardia): string
    {
        return 'recibidos-' . $guardia->date->format('d-m-Y') . '.pdf';
    }
}
