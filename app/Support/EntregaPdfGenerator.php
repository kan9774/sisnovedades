<?php

namespace App\Support;

use App\Models\Entrega;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfContract;

class EntregaPdfGenerator
{
    public static function generar(Entrega $entrega): PdfContract
    {
        self::cargarRelaciones($entrega);

        return Pdf::loadView('livewire.inventario.pdf.comprobante-entrega', [
            'entrega' => $entrega,
        ])->setPaper('a4', 'portrait');
    }

    private static function cargarRelaciones(Entrega $entrega): void
    {
        $entrega->loadMissing([
            'ubicacionOrigen',
            'ubicacionDestino',
            'usuario',
            'movimientos.item',
            'movimientos.itemUnidad',
        ]);
    }

    public static function nombreArchivo(Entrega $entrega): string
    {
        $tipo = $entrega->esDevolucion() ? 'devolucion' : 'entrega';

        return "{$tipo}-{$entrega->id}-" . $entrega->created_at->format('d-m-Y') . '.pdf';
    }
}