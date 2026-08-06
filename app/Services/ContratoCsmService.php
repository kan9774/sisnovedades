<?php

namespace App\Services;

use App\Models\JefeUnidad;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class ContratoCsmService
{
    /**
     * Mapeo lógico -> nombre real del campo en el PDF.
     * Los nombres reales vienen del AcroForm (autogenerados, sin sentido propio),
     * por eso se documenta acá qué representa cada uno.
     */
    private const CAMPOS = [
        // --- Página 1: datos del soldado (mapeo verificado por posición) ---
        'nombre_completo'        => 'Cuadro de texto 1',
        'credencial_serie'       => 'Cuadro de texto 2',
        'credencial_numero'      => 'Cuadro de texto 2_2',
        'cedula'                 => 'Cuadro de texto 2_3',
        'domicilio_calle'        => 'Cuadro de texto 2_4',
        'domicilio_numero'       => 'Cuadro de texto 2_5',
        'domicilio_apto'         => 'Cuadro de texto 2_6',
        'domicilio_barrio'       => 'Cuadro de texto 2_7',
        'domicilio_ciudad'       => 'Cuadro de texto 2_8',
        'domicilio_departamento' => 'Cuadro de texto 2_9',
        'casilla_gubuy'          => 'Cuadro de texto 2_10',
        'contrato_mes_inicio'    => 'Cuadro de texto 2_11',
        'contrato_anio_inicio'   => 'Cuadro de texto 2_12',
        'contrato_dia_fin'       => 'Cuadro de texto 2_13',
        'contrato_mes_fin'       => 'Cuadro de texto 2_14',
        'contrato_anio_fin'      => 'Cuadro de texto 2_15',

        // --- Página 2: autoridad que firma (VERIFICAR con una prueba impresa) ---
        'autoridad_nombre'       => 'Cuadro de texto 2_16',
        'autoridad_cargo'        => 'Cuadro de texto 2_17',
        'autoridad_grado'        => 'Cuadro de texto 2_18',
        'ciudadano_nombre'       => 'Cuadro de texto 2_19', // repite el nombre del soldado

        // --- Página 3: lugar/fecha de suscripción (VERIFICAR) ---
        'suscripcion_lugar'      => 'Cuadro de texto 2_20',
        'suscripcion_dia'        => 'Cuadro de texto 2_21',
        'suscripcion_mes'        => 'Cuadro de texto 2_22',
        'suscripcion_anio'       => 'Cuadro de texto 2_23',

        // Campos 2_24 a 2_29 (bloque de firma / Vo.Bo.): no incluidos todavía,
        // significado ambiguo — se completan a mano por ahora.
    ];

    private const MESES = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    /**
     * Genera el PDF relleno para un usuario, un plazo en años y una fecha de firma.
     * Devuelve la ruta absoluta al PDF generado (temporal).
     */
    public function generar(User $user, int $anios, Carbon $fechaFirma): string
    {
        if ($user->unidad_id !== config('csm.unidad_id')) {
            throw new RuntimeException('Los contratos C.S.M. solo pueden generarse para usuarios de B.Com.Nº1.');
        }

        $plantilla = $this->rutaPlantilla($anios);

        $jefe = JefeUnidad::vigente($fechaFirma);
        if (!$jefe) {
            throw new RuntimeException('No hay un Jefe de Unidad vigente cargado para la fecha de firma.');
        }

        $fechaFin = $fechaFirma->copy()->addYears($anios)->subDay();

        $datos = [
            'nombre_completo'        => $user->name . ' ' . $user->last_name,
            'credencial_serie'       => $user->credencial_serie,
            'credencial_numero'      => $user->credencial_numero,
            'cedula'                 => $user->ci,
            'domicilio_calle'        => $user->domicilio_calle ?? '',
            'domicilio_numero'       => $user->domicilio_numero ?? '',
            'domicilio_apto'         => $user->domicilio_apto ?? '',
            'domicilio_barrio'       => $user->domicilio_barrio ?? '',
            'domicilio_ciudad'       => $user->domicilio_ciudad ?? '',
            'domicilio_departamento' => optional($user->domicilioDepartamento)->nombre ?? '',
            // La casilla institucional .gub.uy usa la cédula (sin puntos ni guión) como local-part.
            'casilla_gubuy'          => $user->ci ?? '',

            'contrato_mes_inicio'    => self::MESES[$fechaFirma->month],
            'contrato_anio_inicio'   => (string) $fechaFirma->year,
            'contrato_dia_fin'       => (string) $fechaFin->day,
            'contrato_mes_fin'       => self::MESES[$fechaFin->month],
            'contrato_anio_fin'      => (string) $fechaFin->year,

            'autoridad_nombre'       => $jefe->nombre_completo,
            'autoridad_cargo'        => $jefe->cargo,
            'autoridad_grado'        => $jefe->grado->nombre,
            'ciudadano_nombre'       => $user->name . ' ' . $user->last_name,

            'suscripcion_lugar'      => config('csm.lugar_suscripcion'),
            'suscripcion_dia'        => (string) $fechaFirma->day,
            'suscripcion_mes'        => self::MESES[$fechaFirma->month],
            'suscripcion_anio'       => (string) $fechaFirma->year,
        ];

        return $this->rellenar($plantilla, $datos, "contrato-csm-{$user->ci}-{$anios}anios.pdf");
    }

    private function rutaPlantilla(int $anios): string
    {
        $archivo = config("csm.plantillas.$anios");
        if (!$archivo) {
            throw new RuntimeException("No hay plantilla configurada para $anios año(s).");
        }

        $ruta = config('csm.plantillas_path') . DIRECTORY_SEPARATOR . $archivo;
        if (!file_exists($ruta)) {
            throw new RuntimeException("Falta el archivo de plantilla: $ruta");
        }

        return $ruta;
    }

    private function rellenar(string $plantilla, array $datos, string $nombreSalida): string
    {
        $fdf = $this->construirFdf($datos);

        $tmpDir = storage_path('app/tmp-csm');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $fdfPath = $tmpDir . DIRECTORY_SEPARATOR . uniqid('csm_') . '.fdf';
        $salidaPath = $tmpDir . DIRECTORY_SEPARATOR . $nombreSalida;

        file_put_contents($fdfPath, $fdf);

        $process = new Process([
            config('csm.pdftk_binary'),
            $plantilla,
            'fill_form',
            $fdfPath,
            'output',
            $salidaPath,
        ]);
        $process->run();

        unlink($fdfPath);

        if (!$process->isSuccessful()) {
            throw new RuntimeException('pdftk falló: ' . $process->getErrorOutput());
        }

        return $salidaPath;
    }

    private function construirFdf(array $datosLogicos): string
    {
        $lineas = [
            '%FDF-1.2',
            '1 0 obj',
            '<< /FDF << /Fields [',
        ];

        foreach (self::CAMPOS as $clave => $campoReal) {
            $valor = $datosLogicos[$clave] ?? '';
            $valorEscapado = $this->escaparFdf((string) $valor);
            $campoEscapado = $this->escaparFdf($campoReal);
            $lineas[] = "<< /T ($campoEscapado) /V ($valorEscapado) >>";
        }

        $lineas[] = '] >> >>';
        $lineas[] = 'endobj';
        $lineas[] = 'trailer';
        $lineas[] = '<< /Root 1 0 R >>';
        $lineas[] = '%%EOF';

        return implode("\n", $lineas);
    }

    private function escaparFdf(string $valor): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $valor);
    }
}