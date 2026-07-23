<?php

namespace App\Support;

use App\Models\Guard;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Arma un ZIP con el PDF de novedades + todos los adjuntos crudos de la
 * guardia (sin embeber, a diferencia de GuardiaPdfGenerator::generarConAdjuntos()).
 * Pensado como alternativa más liviana de procesar que el PDF con adjuntos
 * embebidos — por eso en el componente de envío son mutuamente excluyentes.
 */
class GuardiaZipGenerator
{
    public static function generar(Guard $guardia, string $pdfContent): string
    {
        $guardia->loadMissing('novedades.adjuntos');

        $rutaZip = storage_path('app/temp/guardia_' . $guardia->id . '_' . uniqid() . '.zip');

        if (! is_dir(dirname($rutaZip))) {
            mkdir(dirname($rutaZip), 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString(self::nombreArchivo($guardia), $pdfContent);

        foreach ($guardia->novedades as $novedad) {
            foreach ($novedad->adjuntos as $adjunto) {
                if (! Storage::disk('guardias')->exists($adjunto->file_path)) {
                    continue;
                }

                $contenido = Storage::disk('guardias')->get($adjunto->file_path);
                $nombreEnZip = self::nombreUnicoEnZip($zip, $adjunto->file_name);

                $zip->addFromString($nombreEnZip, $contenido);
            }
        }

        $zip->close();

        $contenidoZip = file_get_contents($rutaZip);
        unlink($rutaZip);

        return $contenidoZip;
    }

    public static function nombreArchivo(Guard $guardia): string
    {
        return 'guardia_' . $guardia->date->format('Y-m-d') . '.zip';
    }

    /**
     * Evita pisar archivos si dos adjuntos de distintas novedades
     * comparten el mismo nombre original (ej. dos "escaneo.pdf").
     */
    protected static function nombreUnicoEnZip(ZipArchive $zip, string $nombre): string
    {
        if ($zip->locateName($nombre) === false) {
            return $nombre;
        }

        $extension = pathinfo($nombre, PATHINFO_EXTENSION);
        $base = pathinfo($nombre, PATHINFO_FILENAME);
        $contador = 1;

        do {
            $candidato = $extension
                ? "{$base}_{$contador}.{$extension}"
                : "{$base}_{$contador}";
            $contador++;
        } while ($zip->locateName($candidato) !== false);

        return $candidato;
    }
}