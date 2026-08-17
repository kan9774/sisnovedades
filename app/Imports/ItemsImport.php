<?php
namespace App\Imports;

use App\Models\Item;
use App\Models\Categoria;
use App\Models\Talla;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ItemsImport
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
            'nombre' => 'required|string',
            'categoria' => 'required|exists:categorias,nombre',
            'tipo_seguimiento' => 'required|in:cantidad,individual',
        ]);

        if ($validator->fails()) {
            $this->errores[] = "Fila {$numeroFila}: " . $validator->errors()->first();
            return;
        }

        $categoria = Categoria::where('nombre', $row['categoria'])->first();
        $talla = !empty($row['talla']) ? Talla::where('valor', $row['talla'])->first() : null;

        Item::create([
            'nombre'            => strtoupper(trim($row['nombre'])),
            'descripcion'       => $row['descripcion'] ?? null,
            'categoria_id'      => $categoria->id,
            'talla_id'          => $talla?->id,
            'tipo_seguimiento'  => $row['tipo_seguimiento'],
            'unidad_medida'     => $row['unidad_medida'] ?? null,
            'stock_minimo'      => $row['stock_minimo'] ?: null,
            'vida_util_meses'   => $row['vida_util_meses'] ?: null,
        ]);

        $this->filasImportadas++;
    }
}
