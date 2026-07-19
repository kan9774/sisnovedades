<?php

namespace App\Support;

use App\Models\Guard;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Symfony\Component\Process\Process;

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
     * IMPORTANTE: este método hace un render completo de DomPDF + una
     * fusión completa de FPDI. Es el mismo resultado para todos los
     * destinatarios de una guardia dada — no lo llames una vez por
     * destinatario en un envío masivo. Generalo una sola vez "afuera"
     * (en el componente que dispara los envíos) y pasá el binario
     * resultante a cada Mailable.
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

        $tmpBase = tempnam(sys_get_temp_dir(), 'guardia_base_') . '.pdf';
        file_put_contents($tmpBase, $pdfBase);

        try {
            $fpdi = new Fpdi();

            // La compresión de FPDF/FPDI (activada por defecto; la dejamos
            // explícita para que quede documentado) comprime con zlib los
            // content streams que FPDI genera al armar el documento final.
            // NO recomprime las imágenes que ya vienen incrustadas dentro
            // de los PDFs de origen, ni las que DomPDF embebió como <img>
            // en el bloque de Anexos: esos bytes se copian tal cual
            // estaban.
            $fpdi->SetCompression(true);

            self::importarPaginas($fpdi, $tmpBase, "PDF base de la guardia {$guardia->id}");

            foreach ($adjuntosPdf as $adjunto) {
                $rutaAdjunto = Storage::disk('guardias')->path($adjunto->file_path);

                if (!file_exists($rutaAdjunto)) {
                    Log::warning("Adjunto omitido al fusionar PDF de guardia {$guardia->id}: no existe el archivo en disco ({$adjunto->file_path}).");
                    continue;
                }

                self::importarPaginas($fpdi, $rutaAdjunto, "adjunto '{$adjunto->file_name}' de la guardia {$guardia->id}");
            }

            return $fpdi->Output('S');
        } finally {
            // Antes el temp se borraba solo al final del flujo feliz: si
            // algo de arriba tiraba una excepción, quedaba huérfano en
            // sys_get_temp_dir() para siempre. Con try/finally se borra
            // siempre, haya salido bien o mal.
            @unlink($tmpBase);
        }
    }

    /**
     * Importa todas las páginas de $rutaPdf al documento FPDI en curso.
     *
     * La versión libre/open-source de FPDI NO soporta PDFs que usan
     * cross-reference streams comprimidos (formato habitual en PDF 1.5+,
     * generado por Chrome, LibreOffice, Prince, etc. — el add-on de pago
     * "FPDI PDF-Parser" es lo único que lo soporta oficialmente). Si
     * setSourceFile() falla, intentamos normalizar el PDF con `qpdf`
     * (--object-streams=disable, que lo reescribe en formato clásico) y
     * reintentamos una sola vez antes de omitirlo.
     *
     * Si el PDF termina omitido, queda logueado — antes se descartaba en
     * silencio con un catch-and-continue, lo que hacía indetectable este
     * tipo de problema (el resultado era simplemente un hueco en blanco
     * en el PDF final, sin ningún rastro del motivo).
     */
    private static function importarPaginas(Fpdi $fpdi, string $rutaPdf, string $etiquetaParaLog): void
    {
        $rutaNormalizada = null;

        try {
            try {
                $totalPaginas = $fpdi->setSourceFile($rutaPdf);
            } catch (\Throwable $e) {
                $rutaNormalizada = self::normalizarPdfConQpdf($rutaPdf);

                if ($rutaNormalizada === null) {
                    Log::warning("PDF omitido al fusionar anexos de guardia ({$etiquetaParaLog}): {$e->getMessage()}");
                    return;
                }

                try {
                    $totalPaginas = $fpdi->setSourceFile($rutaNormalizada);
                } catch (\Throwable $e2) {
                    Log::warning("PDF omitido al fusionar anexos de guardia ({$etiquetaParaLog}) incluso tras normalizar con qpdf: {$e2->getMessage()}");
                    return;
                }
            }

            for ($i = 1; $i <= $totalPaginas; $i++) {
                $tplId = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tplId);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplId);
            }
        } finally {
            if ($rutaNormalizada !== null) {
                @unlink($rutaNormalizada);
            }
        }
    }

    /**
     * Reescribe $rutaOriginal sin xref/object streams comprimidos usando
     * qpdf, en un archivo temporal nuevo. Devuelve null si qpdf no está
     * disponible o falla (quien llama debe manejar ese caso omitiendo
     * el PDF, no hay más fallback posible sin la versión paga de FPDI).
     *
     * Requiere el binario de qpdf instalado en el servidor. En Windows,
     * si no está en el PATH del sistema, configurar la ruta completa via
     * QPDF_BINARY_PATH en el .env (ej: C:\qpdf\bin\qpdf.exe).
     */
    private static function normalizarPdfConQpdf(string $rutaOriginal): ?string
    {
        $rutaSalida = tempnam(sys_get_temp_dir(), 'guardia_qpdf_') . '.pdf';
        $binarioQpdf = env('QPDF_BINARY_PATH', 'qpdf');

        $proceso = new Process([$binarioQpdf, '--object-streams=disable', $rutaOriginal, $rutaSalida]);
        $proceso->setTimeout(30);

        try {
            $proceso->run();
        } catch (\Throwable $e) {
            // Esto salta típicamente cuando el binario no existe / no se
            // puede spawnear (ej: "qpdf" no está en el PATH del sistema).
            Log::warning("No se pudo ejecutar qpdf ('{$binarioQpdf}') para normalizar un PDF: {$e->getMessage()}");
            @unlink($rutaSalida);
            return null;
        }

        if (!$proceso->isSuccessful()) {
            Log::warning("qpdf terminó con error al normalizar un PDF (código de salida {$proceso->getExitCode()}): {$proceso->getErrorOutput()}");
            @unlink($rutaSalida);
            return null;
        }

        if (!file_exists($rutaSalida) || filesize($rutaSalida) === 0) {
            Log::warning('qpdf se ejecutó pero no generó un archivo de salida válido.');
            @unlink($rutaSalida);
            return null;
        }

        return $rutaSalida;
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