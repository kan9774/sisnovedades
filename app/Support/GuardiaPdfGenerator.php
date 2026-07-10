<?php

namespace App\Support;

use App\Models\Guard;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfContract;

class GuardiaPdfGenerator
{
    public static function generar(Guard $guardia): PdfContract
    {
        $guardia->loadMissing([
            'capitan',
            'oficial',
            'escribiente',
            'novedades.organismo',
            'salidasVehiculos.vehiculo',
            'salidasVehiculos.conductor',
            'novedadesPersonal',
            'novedadesRancho.unidad',
        ]);

        return Pdf::loadView('admin.guardias.pdf.novedades', ['guardia' => $guardia])
            ->setPaper('a4', 'portrait');
    }

    public static function nombreArchivo(Guard $guardia): string
    {
        return 'novedades-' . $guardia->date->format('d-m-Y') . '.pdf';
    }
}