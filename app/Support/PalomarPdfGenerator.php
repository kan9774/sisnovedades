<?php

namespace App\Support;

use App\Models\Palomar;
use Barryvdh\DomPDF\PDF as PdfContract;
use Barryvdh\DomPDF\Facade\Pdf;

class PalomarPdfGenerator
{
    public static function generar(Palomar $palomar): PdfContract
    {
        $palomar->load('palomas.estado');

        return Pdf::loadView('admin.palomar.reporte', compact('palomar'))
            ->setPaper('a4', 'portrait');
    }

    public static function nombreArchivo(Palomar $palomar): string
    {
        return 'palomar-' . $palomar->id . '-' . $palomar->nombre . '.pdf';
    }
}
