<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Compresión automática de archivos en el momento de la subida (best-effort).
 *
 * El servicio NUNCA lanza excepciones ni rechaza un archivo: si la compresión
 * falla por cualquier motivo, devuelve el original intacto y loguea un warning.
 *
 * - JPG/JPEG: Intervention Image v4 (driver GD) — corrige orientación EXIF,
 *   reduce a un máximo de 2000px por lado y re-encodea a JPEG progresivo con
 *   calidad 78. Solo se reemplaza el archivo si el resultado es al menos 5%
 *   más chico que el original.
 * - PDF: Ghostscript (`-sDEVICE=pdfwrite -dPDFSETTINGS=/ebook`). La detección
 *   del binario es en runtime; si no está instalado, los PDF pasan sin tocar.
 *
 * ⚠️ TODO (Ghostscript): al 2026-08-21 Ghostscript NO está instalado en este
 * servidor de producción IIS (verificado: no está en PATH ni en
 * C:\Program Files\gs). La compresión de PDF queda implementada e inactiva:
 * cuando se instale Ghostscript (instalador oficial Windows → agrega
 * gswin64c.exe al PATH, o `apt install ghostscript` en Linux) NO hace falta
 * cambiar código — la detección runtime lo habilita sola. Verificar después
 * de instalar con: CompresorArchivos::puedeComprimirPdf() === true.
 */
class CompresorArchivos
{
    /**
     * Máximo lado (ancho o alto) tras el reescalado. Un documento/foto sigue
     * siendo perfectamente legible a 2000px y típicamente pesa < 1MB.
     */
    private const IMAGEN_MAX_LADO_PX = 2000;

    /** Calidad JPEG del re-encode (0-100). 78 = equilibrio peso/legibilidad. */
    private const IMAGEN_CALIDAD_JPEG = 78;

    /**
     * Solo se reemplaza el original si el comprimido pesa menos que esta
     * fracción del original (0.95 = ahorro mínimo del 5%). Evita crecer
     * archivos ya optimizados.
     */
    private const FACTOR_AHORRO_MINIMO = 0.95;

    /**
     * No decodificar imágenes cuyo tamaño en píxeles supere este límite:
     * GD aloja ~4.3 bytes/píxel y una foto gigante puede agotar memory_limit
     * (fatal NO capturable). 30MP cubre cualquier foto de documento real.
     */
    private const IMAGEN_MAX_PIXELS = 30_000_000;

    /** Timeout del proceso Ghostscript en segundos. */
    private const GS_TIMEOUT_SEGUNDOS = 120;

    /** Binario gs resuelto (cacheado por proceso). Null = no disponible. */
    private static ?string $binarioGs = null;

    private static bool $gsResuelto = false;

    /**
     * Punto de entrada. Recibe el archivo subido (TemporaryUploadedFile de
     * Livewire o UploadedFile) y devuelve UN archivo listo para almacenar:
     * el comprimido si valió la pena, u el original si no corresponde comprimir.
     */
    public static function comprimir(UploadedFile $archivo): UploadedFile
    {
        try {
            $extension = strtolower($archivo->getClientOriginalExtension());

            $resultado = match ($extension) {
                'jpg', 'jpeg' => self::comprimirImagen($archivo),
                'pdf' => self::comprimirPdf($archivo),
                default => $archivo,
            };

            return self::asegurarRutaResoluble($resultado);
        } catch (Throwable $e) {
            Log::warning('CompresorArchivos: compresión fallida, se conserva el original.', [
                'archivo' => $archivo->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return self::asegurarRutaResoluble($archivo);
        }
    }

    /**
     * Indica si hay un binario de Ghostscript utilizable en este entorno.
     */
    public static function puedeComprimirPdf(): bool
    {
        return self::resolverBinarioGs() !== null;
    }

    // ── Imágenes JPG/JPEG ────────────────────────────────────────────────────

    private static function comprimirImagen(UploadedFile $archivo): UploadedFile
    {
        // getRealPath() solo NO basta en este IIS: para temporales recién
        // escritos en el temp del sistema devuelve false aunque el archivo
        // exista. rutaAccesible() cae a la ruta literal (getPathname()).
        $rutaOrigen = self::rutaAccesible($archivo);

        if ($rutaOrigen === null) {
            return $archivo;
        }

        // Guardia anti-OOM: leer dimensiones del header es barato; decodificar
        // una imagen enorme en GD no lo es (y un fatal de memoria no es capturable).
        $info = @getimagesize($rutaOrigen);
        if ($info === false) {
            return $archivo; // no es una imagen válida: fail-open
        }
        [$ancho, $alto] = $info;
        if (($ancho * $alto) > self::IMAGEN_MAX_PIXELS) {
            return $archivo;
        }

        // Leer por CONTENIDO y decodificar desde el string: el lector de
        // archivos de Intervention hace un preflight de legibilidad sobre el
        // DIRECTORIO contenedor, y en este IIS los directorios del temp del
        // sistema no son listables para la identidad del app pool, aunque la
        // lectura del archivo puntual sí funciona (verificado con sha1_file).
        // decodePath() lanzaba "Directory is not readable" y mataba la
        // compresión para cualquier archivo que viva ahí.
        $contenido = @file_get_contents($rutaOrigen);

        if ($contenido === false || $contenido === '') {
            return $archivo;
        }

        $manager = new ImageManager(GdDriver::class);
        $imagen = $manager->decode($contenido);

        $datos = (string) $imagen->orient()
            ->scaleDown(self::IMAGEN_MAX_LADO_PX, self::IMAGEN_MAX_LADO_PX)
            ->encode(new JpegEncoder(
                quality: self::IMAGEN_CALIDAD_JPEG,
                progressive: true,
            ));

        return self::envolverSiConviene($archivo, $datos);
    }

    // ── PDF vía Ghostscript ──────────────────────────────────────────────────

    private static function comprimirPdf(UploadedFile $archivo): UploadedFile
    {
        $binario = self::resolverBinarioGs();

        // Ghostscript no instalado: los PDF pasan sin tocar (ver TODO arriba).
        if ($binario === null) {
            return $archivo;
        }

        $realPath = self::rutaAccesible($archivo);
        if ($realPath === null) {
            return $archivo;
        }

        $salida = tempnam(sys_get_temp_dir(), 'gs_') . '.pdf';

        $proceso = new Process([
            $binario,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.5',
            '-dPDFSETTINGS=/ebook', // 150 dpi: calidad lectura, peso ~1/4
            '-dNOPAUSE',
            '-dBATCH',
            '-dSAFER',
            '-dQUIET',
            '-sOutputFile=' . $salida,
            $realPath,
        ]);
        $proceso->setTimeout(self::GS_TIMEOUT_SEGUNDOS);
        $proceso->run();

        if (! $proceso->isSuccessful()) {
            @unlink($salida);

            throw new \RuntimeException('Ghostscript falló: ' . $proceso->getErrorOutput());
        }

        $datos = file_exists($salida) ? file_get_contents($salida) : false;

        if ($datos === false || ! str_starts_with($datos, '%PDF')) {
            @unlink($salida);

            throw new \RuntimeException('Ghostscript no produjo un PDF válido.');
        }

        return self::envolverSiConviene($archivo, $datos, $salida);
    }

    /**
     * Ubicación del binario Ghostscript, o null si no existe en el entorno.
     * Resuelto una sola vez por proceso y cacheado.
     */
    private static function resolverBinarioGs(): ?string
    {
        if (self::$gsResuelto) {
            return self::$binarioGs;
        }

        self::$gsResuelto = true;
        self::$binarioGs = self::detectarBinarioGs();

        if (self::$binarioGs === null) {
            Log::info('CompresorArchivos: Ghostscript no disponible; los PDF se guardan sin comprimir.');
        }

        return self::$binarioGs;
    }

    private static function detectarBinarioGs(): ?string
    {
        $candidatos = [];

        if (PHP_OS_FAMILY === 'Windows') {
            // Instalador oficial: C:\Program Files\gs\gs<version>\bin\gswin64c.exe
            foreach (['C:/Program Files/gs', 'C:/Program Files (x86)/gs'] as $base) {
                foreach (glob($base . '/gs*/bin/gswin*c.exe') ?: [] as $ruta) {
                    $candidatos[] = $ruta;
                }
            }
            $candidatos[] = 'gswin64c.exe';
            $candidatos[] = 'gswin32c.exe';
        } else {
            $candidatos[] = 'gs';
        }

        foreach ($candidatos as $candidato) {
            if (str_contains($candidato, DIRECTORY_SEPARATOR)) {
                // Ruta absoluta candidata: basta con que exista y sea ejecutable
                if (is_file($candidato)) {
                    return $candidato;
                }

                continue;
            }

            // Nombre corto en PATH: probar invocándolo
            $probe = new Process([$candidato, '--version']);
            $probe->setTimeout(10);

            try {
                $probe->run();
            } catch (Throwable) {
                continue; // binario inexistente: Process puede lanzar
            }

            if ($probe->isSuccessful()) {
                return $candidato;
            }
        }

        return null;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Ruta real utilizable del archivo de entrada, o null si es inaccesible.
     *
     * En este servidor IIS, realpath() devuelve false para temporales recién
     * escritos en el temp del sistema aunque el archivo exista (verificado
     * 2026-08-21 bajo FastCGI con la identidad del app pool: stat/is_file
     * funcionan; la resolución de realpath no). Por eso NO basta con
     * getRealPath(): si devuelve vacío/falso pero el archivo existe en su ruta
     * literal (getPathname()), se usa esa.
     */
    private static function rutaAccesible(UploadedFile $archivo): ?string
    {
        $realPath = $archivo->getRealPath();

        if (is_string($realPath) && $realPath !== '' && is_file($realPath)) {
            return $realPath;
        }

        $pathname = $archivo->getPathname();

        if (is_string($pathname) && $pathname !== '' && is_file($pathname)) {
            return $pathname;
        }

        return null;
    }

    /**
     * Garantiza que lo que sale de comprimir() sea almacenable por los
     * call-sites (storeAs → putFileAs → fopen(getRealPath())).
     *
     * Si el objeto devuelto tiene una ruta que realpath() no puede resolver
     * PERO el archivo existe en su ruta literal, se envuelve en
     * ArchivoComprimido para congelar la ruta. Es la misma anomalía de IIS
     * vista desde el lado del original (ej.: fail-open sobre un archivo cuyo
     * getRealPath() falla); sin esto, el storeAs() del caller explotaría con
     * ValueError "Path must not be empty" aunque la compresión no aplicara.
     */
    private static function asegurarRutaResoluble(UploadedFile $archivo): UploadedFile
    {
        $realPath = $archivo->getRealPath();

        if ((is_string($realPath) && $realPath !== '') || ! is_file($archivo->getPathname())) {
            return $archivo;
        }

        return new ArchivoComprimido($archivo->getPathname(), $archivo->getClientOriginalName());
    }


    /**
     * Devuelve un UploadedFile nuevo con el contenido comprimido si pesa al
     * menos FACTOR_AHORRO_MINIMO veces menos que el original; si no, el original.
     *
     * El nuevo archivo vive en el directorio temporal del sistema (fuera de
     * livewire-tmp, para no interferir con la GC de Livewire) y conserva el
     * nombre original del cliente, así los call-sites siguen usando
     * storeAs()/getClientOriginalName()/getSize() sin cambios.
     *
     * Se devuelve un ArchivoComprimido (NO un UploadedFile plano): su override
     * de getRealPath() devuelve la ruta literal sin pasar por realpath(), que
     * en IIS puede devolver false sobre temporales recién escritos aunque el
     * archivo exista → fopen(false) → ValueError "Path must not be empty".
     */
    private static function envolverSiConviene(UploadedFile $original, string|false $datos, ?string $rutaTemporal = null): UploadedFile
    {
        if ($datos === false || $datos === '') {
            return $original;
        }

        $tamanioOriginal = $original->getSize() ?: strlen($datos);

        if (strlen($datos) > ($tamanioOriginal * self::FACTOR_AHORRO_MINIMO)) {
            if ($rutaTemporal !== null) {
                @unlink($rutaTemporal);
            }

            return $original;
        }

        if ($rutaTemporal !== null) {
            $rutaFinal = $rutaTemporal;
        } else {
            $rutaFinal = tempnam(sys_get_temp_dir(), 'cmp_');
            file_put_contents($rutaFinal, $datos);
        }

        // Barrera fail-open: si el temporal no quedó accesible en disco,
        // devolver el original intacto (nunca un wrapper sobre algo inexistente).
        if (! is_file($rutaFinal)) {
            Log::warning('CompresorArchivos: temporal inaccesible, se devuelve original sin comprimir.', [
                'archivo' => $original->getClientOriginalName(),
                'ruta_temporal' => $rutaFinal,
            ]);

            return $original;
        }

        return new ArchivoComprimido($rutaFinal, $original->getClientOriginalName());
    }
}
