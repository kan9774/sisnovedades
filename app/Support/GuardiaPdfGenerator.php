<?php

namespace App\Support;

use App\Models\Guard;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfContract;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class GuardiaPdfGenerator
{
    /**
     * PDF de novedades "simple" — el de siempre, sin tocar.
     * Se usa para el preview/render en la landing y para el envío
     * estándar por correo (sin adjuntos incrustados).
     */
    public static function generar(Guard $guardia): PdfContract
    {
        self::cargarRelaciones($guardia);

        return Pdf::loadView('admin.guardias.pdf.novedades', [
            'guardia' => $guardia,
            'incluirAdjuntos' => false,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * PDF de novedades + Anexos: mismo Blade que generar(), pero con
     * $incluirAdjuntos=true activa el bloque de Anexos (encabezado de
     * cada "Recibido": origen, fecha/hora, oficina, quien lo tomó +
     * imágenes embebidas), y fusiona al final las páginas de los PDFs
     * adjuntos con FPDI.
     *
     * Usar cuando el destinatario pidió explícitamente ver lo recibido.
     *
     * @return string Contenido binario del PDF final
     */
    public static function generarConAdjuntos(Guard $guardia): string
    {
        self::cargarRelaciones($guardia);

        $pdfBase = Pdf::loadView('admin.guardias.pdf.novedades', [
            'guardia' => $guardia,
            'incluirAdjuntos' => true,
        ])->setPaper('a4', 'portrait')->output();

        return self::fusionarAdjuntosPdf($pdfBase, $guardia);
    }

    private static function cargarRelaciones(Guard $guardia): void
    {
        $guardia->loadMissing([
            'capitan',
            'oficial',
            'escribiente',
            'novedades.organismo',
            'novedades.oficina',
            'novedades.tomadoPor',
            'novedades.adjuntos',
            'salidasVehiculos.vehiculo',
            'salidasVehiculos.conductor',
            'novedadesPersonal',
            'novedadesRancho.unidad',
        ]);
    }

    private static function fusionarAdjuntosPdf(string $pdfBase, Guard $guardia): string
    {
        $adjuntosPdf = $guardia->novedades
            ->where('direction', 'Recibido')
            ->flatMap(fn ($novedad) => $novedad->adjuntos)
            ->filter(fn ($adjunto) => $adjunto->esPdf())
            ->values();

        if ($adjuntosPdf->isEmpty()) {
            return $pdfBase;
        }

        $fpdi = new Fpdi();

        $tmpBase = tempnam(sys_get_temp_dir(), 'guardia_base_') . '.pdf';
        file_put_contents($tmpBase, $pdfBase);

        $totalPaginasBase = $fpdi->setSourceFile($tmpBase);
        for ($i = 1; $i <= $totalPaginasBase; $i++) {
            $tplId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tplId);
            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($tplId);
        }

        foreach ($adjuntosPdf as $adjunto) {
            $rutaAdjunto = Storage::disk('guardias')->path($adjunto->file_path);

            if (!file_exists($rutaAdjunto)) {
                continue;
            }

            try {
                $totalPaginasAdjunto = $fpdi->setSourceFile($rutaAdjunto);
            } catch (\Throwable $e) {
                continue;
            }

            for ($i = 1; $i <= $totalPaginasAdjunto; $i++) {
                $tplId = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tplId);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);
            }
        }

        @unlink($tmpBase);

        return $fpdi->Output('S');
    }

    public static function nombreArchivo(Guard $guardia): string
    {
        return 'novedades-' . $guardia->date->format('d-m-Y') . '.pdf';
    }

    public static function nombreArchivoConAdjuntos(Guard $guardia): string
    {
        return 'novedades-con-anexos-' . $guardia->date->format('d-m-Y') . '.pdf';
    }
}