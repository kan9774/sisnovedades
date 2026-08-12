<?php
namespace App\Imports;

use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\Proveedor;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class ItemUnidadesImport
{
    public int $filasImportadas = 0;
    public array $errores = [];

    public function importar(string $rutaArchivo): void
    {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();
        $filas = $hoja->toArray(null, true, true, false);

        $encabezados = array_map('trim', array_shift($filas));

        foreach ($filas as $numeroFila => $fila) {
            if (empty(array_filter($fila))) continue;

            $datos = array_combine($encabezados, $fila);
            $this->procesarFila($datos, $numeroFila + 2);
        }
    }

    private function procesarFila(array $row, int $numeroFila): void
    {
        $validator = Validator::make($row, [
            'codigo_item' => 'required|string|exists:items,codigo',
            'estado' => 'nullable|in:disponible,asignado,en_reparacion,baja',
        ]);

        if ($validator->fails()) {
            $this->errores[] = "Fila {$numeroFila}: " . $validator->errors()->first();
            return;
        }

        $item = Item::where('codigo', strtoupper(trim($row['codigo_item'])))->first();
        $proveedor = !empty($row['proveedor']) ? Proveedor::where('nombre', $row['proveedor'])->first() : null;
        $ubicacion = !empty($row['ubicacion']) ? Ubicacion::where('nombre', $row['ubicacion'])->first() : null;
        $responsable = !empty($row['responsable_ci']) ? User::where('ci', $row['responsable_ci'])->first() : null;

        ItemUnidad::create([
            'item_id'             => $item->id,
            'numero_serie'        => $row['numero_serie'] ?? null,
            'estado'              => $row['estado'] ?: 'disponible',
            'proveedor_id'        => $proveedor?->id,
            'fecha_recibido'      => $this->parsearFecha($row['fecha_recibido'] ?? null),
            'ubicacion_actual_id' => $ubicacion?->id,
            'responsable_id'      => $responsable?->id,
            'fecha_alta'          => $this->parsearFecha($row['fecha_alta'] ?? null),
        ]);

        $this->filasImportadas++;
    }

    private function parsearFecha($valor)
    {
        if (empty($valor)) return null;

        return is_numeric($valor)
            ? ExcelDate::excelToDateTimeObject($valor)->format('Y-m-d')
            : Carbon::parse($valor)->format('Y-m-d');
    }
}