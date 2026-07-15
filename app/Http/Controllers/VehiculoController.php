<?php

namespace App\Http\Controllers;

use App\Models\TipoCombustible;
use App\Models\TipoLubricante;
use App\Models\TipoRodado;
use App\Models\TipoVehiculo;
use App\Models\Unidad;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VehiculoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Vehiculo::class);

        $vehiculos = Vehiculo::with(['tipoVehiculo', 'tipoCombustible', 'tipoLubricante', 'tipoRodado'])
            ->orderBy('matricula')
            ->paginate(15);

        return view('admin.vehiculos.index', compact('vehiculos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Vehiculo::class);

        $tiposVehiculo = TipoVehiculo::where('activo', true)->orderBy('nombre')->get();
        $tiposCombustible = TipoCombustible::where('activo', true)->orderBy('nombre')->get();
        $tiposLubricante = TipoLubricante::where('activo', true)->orderBy('nombre')->get();
        $tiposRodado = TipoRodado::where('activo', true)->orderBy('nombre')->get();
        $unidades = Unidad::where('activo', true)->orderBy('nombre')->get();

        return view('admin.vehiculos.create', compact(
            'tiposVehiculo',
            'tiposCombustible',
            'tiposLubricante',
            'tiposRodado',
            'unidades'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Vehiculo::class);

        $data = $request->validate([
            'matricula' => 'required|string|max:20|unique:vehiculos,matricula',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'vehiculo' => 'nullable|string|max:50',
            'numero_chasis' => 'nullable|string|max:50|unique:vehiculos,numero_chasis',
            'numero_motor' => 'nullable|string|max:50|unique:vehiculos,numero_motor',
            'ejes' => 'nullable|integer|min:1|max:10',
            'tipo_vehiculo_id' => 'nullable|exists:tipos_vehiculo,id',
            'unidad_id' => 'nullable|exists:unidades,id',
            'tipo_combustible_id' => 'required|exists:tipos_combustible,id',
            'tipo_lubricante_id' => 'nullable|exists:tipos_lubricante,id',
            'tipo_rodado_id' => 'nullable|exists:tipos_rodado,id',
            'consumo_litros_por_km' => 'nullable|numeric|min:0|max:999.9999',
            'sin_cuentakilometros' => 'boolean',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'required|in:verde,amarillo,rojo,negro',
        ]);

        $data['sin_cuentakilometros'] = $request->has('sin_cuentakilometros');
        $data['activo'] = $request->has('activo') && !in_array($data['estado'], ['rojo', 'negro']);

        Vehiculo::create($data);
        $mensaje = $data['activo']
            ? 'Vehículo creado correctamente.'
            : 'Vehículo creado correctamente, pero se encuentra inactivo porque el estado es rojo o negro.';

        return redirect()->route('admin.vehiculos.index')
            ->with('success', $mensaje);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehiculo $vehiculo)
    {
        $this->authorize('view', $vehiculo);

        $vehiculo->load([
            'tipoVehiculo',
            'tipoCombustible',
            'tipoLubricante',
            'tipoRodado',
            'salidas' => function ($query) {
                $query->with(['guardia', 'conductor'])->latest('id')->limit(10);
            },
            'mantenimientos' => function ($query) {
                $query->latest('fecha')->limit(10);
            },
        ]);

        return view('admin.vehiculos.show', compact('vehiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehiculo $vehiculo)
    {
        $this->authorize('update', $vehiculo);

        $tiposVehiculo = TipoVehiculo::where('activo', true)
            ->orWhere('id', $vehiculo->tipo_vehiculo_id)
            ->orderBy('nombre')
            ->get();
        $tiposCombustible = TipoCombustible::where('activo', true)
            ->orWhere('id', $vehiculo->tipo_combustible_id)
            ->orderBy('nombre')
            ->get();
        $tiposLubricante = TipoLubricante::where('activo', true)
            ->orWhere('id', $vehiculo->tipo_lubricante_id)
            ->orderBy('nombre')
            ->get();
        $tiposRodado = TipoRodado::where('activo', true)
            ->orWhere('id', $vehiculo->tipo_rodado_id)
            ->orderBy('nombre')
            ->get();
        $unidades = Unidad::where('activo', true)
            ->orWhere('id', $vehiculo->unidad_id)
            ->orderBy('nombre')
            ->get();

        return view('admin.vehiculos.edit', compact(
            'vehiculo',
            'tiposVehiculo',
            'tiposCombustible',
            'tiposLubricante',
            'tiposRodado',
            'unidades'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        $this->authorize('update', $vehiculo);

        $data = $request->validate([
            'matricula' => 'required|string|max:20|unique:vehiculos,matricula,' . $vehiculo->id,
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'vehiculo' => 'nullable|string|max:50',
            'numero_chasis' => 'nullable|string|max:50|unique:vehiculos,numero_chasis,' . $vehiculo->id,
            'numero_motor' => 'nullable|string|max:50|unique:vehiculos,numero_motor,' . $vehiculo->id,
            'ejes' => 'nullable|integer|min:1|max:10',
            'unidad_id' => 'nullable|integer|exists:unidades,id',
            'tipo_vehiculo_id' => 'nullable|integer|exists:tipos_vehiculo,id',
            'tipo_combustible_id' => 'required|integer|exists:tipos_combustible,id',
            'tipo_lubricante_id' => 'nullable|integer|exists:tipos_lubricante,id',
            'tipo_rodado_id' => 'nullable|integer|exists:tipos_rodado,id',
            'consumo_litros_por_km' => 'nullable|numeric|min:0|max:999.9999',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'required|in:verde,amarillo,rojo,negro',
        ]);

        $data['sin_cuentakilometros'] = $request->has('sin_cuentakilometros');
        $activo = $request->has('activo') && !in_array($data['estado'], ['rojo', 'negro']);
        $data['activo'] = $activo;
        $vehiculo->update($data);

        $mensaje = $activo
            ? 'Vehículo actualizado correctamente'
            : 'Vehículo actualizado y desactivado automáticamente (estado rojo/negro)';
        return redirect()->route('admin.vehiculos.index')
            ->with('success', $mensaje);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        $this->authorize('delete', $vehiculo);

        // Verificar si tiene salidas asociadas
        if ($vehiculo->salidas()->count() > 0) {
            return redirect()->route('admin.vehiculos.index')
                ->with('error', 'No se puede eliminar un vehículo con salidas asociadas.');
        }

        $vehiculo->delete();

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }

    /**
     * Export vehículos to Excel, grouped by tipo de vehículo.
     */
    public function export()
    {
        $this->authorize('viewAny', Vehiculo::class);

        $vehiculos = Vehiculo::with(['tipoVehiculo', 'unidad', 'tipoCombustible', 'tipoLubricante', 'tipoRodado'])
            ->orderBy('matricula')
            ->get()
            ->groupBy(fn($v) => $v->tipoVehiculo->nombre ?? 'Sin Tipo')
            ->sortKeys();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vehículos');

        $sheet->getColumnDimension('B')->setWidth(9.86);
        $sheet->getColumnDimension('C')->setWidth(11.29);
        $sheet->getColumnDimension('D')->setWidth(14.29);
        $sheet->getColumnDimension('E')->setWidth(10.29);
        $sheet->getColumnDimension('F')->setWidth(11.29);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(14.86);
        $sheet->getColumnDimension('I')->setWidth(9.57);
        $sheet->getColumnDimension('J')->setWidth(10.14);

        $headers = ['Unidad', 'Vehículo', 'Matricula', 'Marca', 'Modelo', 'Estado', 'Descripcion', 'Tipo de Comb.'];

        $titleStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'italic' => true],
        ];
        $headerStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'italic' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $dataStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 11],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $row = 3;

        foreach ($vehiculos as $tipoNombre => $items) {
            $sheet->setCellValue("B{$row}", $tipoNombre);
            $sheet->getStyle("B{$row}")->applyFromArray($titleStyle);
            $row++;

            $col = 'B';
            foreach ($headers as $h) {
                $sheet->setCellValue("{$col}{$row}", $h);
                $col++;
            }
            $sheet->mergeCells("I{$row}:J{$row}");
            $sheet->getStyle("B{$row}:J{$row}")->applyFromArray($headerStyle);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;

            foreach ($items as $v) {
                $sheet->setCellValue("B{$row}", $v->unidad->nombre ?? '-');
                $sheet->setCellValue("C{$row}", $v->vehiculo ?? '-');
                $sheet->setCellValue("D{$row}", $v->matricula);
                $sheet->setCellValue("E{$row}", $v->marca ?? '-');
                $sheet->setCellValue("F{$row}", $v->modelo ?? '-');
                $sheet->setCellValue("G{$row}", match ($v->estado) {
                    'verde' => 'V',
                    'amarillo' => 'A',
                    'rojo' => 'R',
                    'negro' => 'N',
                    default => '-',
                });
                $sheet->setCellValue("H{$row}", $v->descripcion ?? '-');
                $sheet->setCellValue("I{$row}", $v->tipoCombustible->nombre ?? '-');
                $sheet->mergeCells("I{$row}:J{$row}");
                $sheet->getStyle("B{$row}:J{$row}")->applyFromArray($dataStyle);
                $row++;
            }

            $row += 2;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'vehiculos_' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}