<?php

namespace App\Support;

/**
 * Comprime y redimensiona imágenes antes de embeberlas como base64 en el
 * PDF de Anexos. Sin esto, se embebe el archivo original tal cual vino
 * (una foto de celular puede pesar 3-5 MB), lo que infla muchísimo el PDF
 * final — y eso NO lo arregla FPDI ni SetCompression, porque FPDI nunca
 * toca estos bytes (los genera DomPDF a partir del <img> del Blade).
 *
 * Usa GD (viene con PHP por defecto, no requiere Imagick).
 */
class ImagenPdfCompresor
{
    private const ANCHO_MAXIMO = 1200; // px — de sobra para verse nítido en A4
    private const CALIDAD_JPEG = 72;   // 0-100, buen balance peso/calidad

    /**
     * Devuelve una data URI (data:image/jpeg;base64,...) lista para usar
     * en un <img src="..."> de DomPDF, ya redimensionada y recomprimida.
     * Si algo falla (formato no soportado, archivo corrupto, etc.),
     * devuelve null y quien llama debe mostrar el fallback de "no se
     * pudo cargar la imagen".
     */
    public static function comprimirParaEmbeber(string $rutaCompleta, string $mimeType): ?string
    {
        if (!file_exists($rutaCompleta)) {
            return null;
        }

        $imagenOriginal = self::crearImagenDesdeArchivo($rutaCompleta, $mimeType);

        if ($imagenOriginal === null) {
            return null;
        }

        try {
            $anchoOriginal = imagesx($imagenOriginal);
            $altoOriginal = imagesy($imagenOriginal);

            if ($anchoOriginal <= 0 || $altoOriginal <= 0) {
                return null;
            }

            // Redimensionar solo si excede el ancho máximo (no agrandar imágenes chicas)
            if ($anchoOriginal > self::ANCHO_MAXIMO) {
                $anchoNuevo = self::ANCHO_MAXIMO;
                $altoNuevo = (int) round($altoOriginal * ($anchoNuevo / $anchoOriginal));

                $imagenRedimensionada = imagecreatetruecolor($anchoNuevo, $altoNuevo);

                // Fondo blanco (por si la original tenía transparencia:
                // convertimos todo a JPEG, que no soporta canal alfa)
                $blanco = imagecolorallocate($imagenRedimensionada, 255, 255, 255);
                imagefilledrectangle($imagenRedimensionada, 0, 0, $anchoNuevo, $altoNuevo, $blanco);

                imagecopyresampled(
                    $imagenRedimensionada, $imagenOriginal,
                    0, 0, 0, 0,
                    $anchoNuevo, $altoNuevo, $anchoOriginal, $altoOriginal
                );

                imagedestroy($imagenOriginal);
                $imagenFinal = $imagenRedimensionada;
            } else {
                // No hace falta redimensionar, pero igual la volcamos sobre
                // fondo blanco para poder recomprimir como JPEG si venía
                // como PNG/GIF con transparencia.
                $imagenFinal = imagecreatetruecolor($anchoOriginal, $altoOriginal);
                $blanco = imagecolorallocate($imagenFinal, 255, 255, 255);
                imagefilledrectangle($imagenFinal, 0, 0, $anchoOriginal, $altoOriginal, $blanco);
                imagecopy($imagenFinal, $imagenOriginal, 0, 0, 0, 0, $anchoOriginal, $altoOriginal);
                imagedestroy($imagenOriginal);
            }

            ob_start();
            imagejpeg($imagenFinal, null, self::CALIDAD_JPEG);
            $contenidoComprimido = ob_get_clean();
            imagedestroy($imagenFinal);

            if ($contenidoComprimido === false || $contenidoComprimido === '') {
                return null;
            }

            return 'data:image/jpeg;base64,' . base64_encode($contenidoComprimido);
        } catch (\Throwable $e) {
            if (isset($imagenOriginal) && is_resource($imagenOriginal) || $imagenOriginal instanceof \GdImage) {
                imagedestroy($imagenOriginal);
            }

            return null;
        }
    }

    /**
     * @return \GdImage|null
     */
    private static function crearImagenDesdeArchivo(string $rutaCompleta, string $mimeType)
    {
        try {
            return match (true) {
                str_contains($mimeType, 'jpeg'), str_contains($mimeType, 'jpg') => imagecreatefromjpeg($rutaCompleta),
                str_contains($mimeType, 'png') => imagecreatefrompng($rutaCompleta),
                str_contains($mimeType, 'gif') => imagecreatefromgif($rutaCompleta),
                str_contains($mimeType, 'webp') => function_exists('imagecreatefromwebp')
                    ? imagecreatefromwebp($rutaCompleta)
                    : null,
                str_contains($mimeType, 'bmp') => function_exists('imagecreatefrombmp')
                    ? imagecreatefrombmp($rutaCompleta)
                    : null,
                default => null,
            } ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}