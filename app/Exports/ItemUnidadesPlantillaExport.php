<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ItemUnidadesPlantillaExport
{
    public function generar(): string
    {
        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        $encabezados = ['codigo_item', 'numero_serie', 'estado', 'proveedor', 'fecha_recibido', 'ubicacion', 'responsable_ci', 'fecha_alta'];
        $hoja->fromArray($encabezados, null, 'A1');
        $hoja->getStyle('A1:H1')->getFont()->setBold(true);

        $ejemplo = ['UNIF-CAMP-01', 'SN-00123', 'disponible', 'Proveedor S.A.', '2026-07-01', 'Depósito Central', '1234567-8', '2026-07-05'];
        $hoja->fromArray($ejemplo, null, 'A2');

        $rutaTemp = storage_path('app/temp/plantilla_item_unidades_' . uniqid() . '.xlsx');
        if (!is_dir(dirname($rutaTemp))) mkdir(dirname($rutaTemp), 0755, true);

        (new Xlsx($spreadsheet))->save($rutaTemp);

        return $rutaTemp;
    }
}