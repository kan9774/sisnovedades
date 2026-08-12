<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ItemsPlantillaExport
{
    public function generar(): string
    {
        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        $encabezados = ['codigo', 'nombre', 'categoria', 'talla', 'tipo_seguimiento', 'unidad_medida', 'stock_minimo', 'vida_util_meses'];
        $hoja->fromArray($encabezados, null, 'A1');
        $hoja->getStyle('A1:H1')->getFont()->setBold(true);

        $ejemplo = ['UNIF-CAMP-01', 'UNIFORME DE CAMPAÑA', 'Vestuario', 'M', 'cantidad', 'unidad', 10, 24];
        $hoja->fromArray($ejemplo, null, 'A2');

        $rutaTemp = storage_path('app/temp/plantilla_items_' . uniqid() . '.xlsx');
        if (!is_dir(dirname($rutaTemp))) mkdir(dirname($rutaTemp), 0755, true);

        (new Xlsx($spreadsheet))->save($rutaTemp);

        return $rutaTemp;
    }
}