<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Wrapper de UploadedFile para los archivos que CompresorArchivos reescribe
 * en un temporal del sistema.
 *
 * ⚠️ Por qué existe: el UploadedFile plano hereda getRealPath() de
 * SplFileInfo, que RE-resuelve la ruta vía realpath(). En este servidor IIS
 * esa resolución devuelve FALSE para temporales recién escritos aunque el
 * archivo exista (candidatos: barrido de antivirus sobre .tmp, ACLs de la
 * identidad del app pool, 8.3 names deshabilitados). El resultado era
 * fopen(false) dentro de FilesystemAdapter::putFileAs() → ValueError
 * "Path must not be empty" → HTTP 500 en la subida.
 *
 * Esta clase congela la ruta literal usada al construir el objeto:
 * getRealPath() la devuelve SIEMPRE, sin pasar por realpath(), de modo que
 * store()/storeAs()/putFileAs() abren el archivo por su ruta real aunque la
 * resolución de realpath falle.
 *
 * getPathname(), getSize(), getMimeType(), getClientOriginalName(),
 * hashName(), etc. no necesitan override: operan sobre la misma ruta literal
 * o sobre el nombre pasado al constructor padre.
 *
 * Fail-open garantizado aguas arriba: CompresorArchivos solo construye esta
 * clase tras verificar is_file() sobre la ruta; si el temporal no existe,
 * devuelve el archivo original intacto.
 *
 * También se usa para envolver ORIGINALES cuya ruta literal existe pero cuya
 * resolución realpath() falla (misma anomalía de IIS, vista desde el lado del
 * archivo de entrada): ver CompresorArchivos::asegurarRutaResoluble().
 */
final class ArchivoComprimido extends UploadedFile
{
    private string $ruta;

    public function __construct(string $ruta, string $nombreOriginal)
    {
        parent::__construct($ruta, $nombreOriginal, null, null, true);

        $this->ruta = $ruta;
    }

    /**
     * Devuelve la ruta literal del temporal, SIN re-resolver vía realpath().
     * (SplFileInfo::getRealPath() devuelve false cuando realpath() no puede
     * resolver la ruta aunque el archivo exista — anomalía observada en IIS.)
     */
    public function getRealPath(): string
    {
        return $this->ruta;
    }
}
