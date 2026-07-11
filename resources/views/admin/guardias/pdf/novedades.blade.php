<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header {
            margin-bottom: 10px;
            margin-top: 10px;
            margin-left: 20px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h3 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .seccion {
            margin-bottom: 12px;
        }

        .seccion-titulo {
            margin-left: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            font-size: 11px;
        }

        table {
            width: 95%;
            border-collapse: collapse;
            margin-bottom: 6px;
            margin: 0 auto;
        }

        table th {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 3px 4px;
            font-size: 10px;
            font-weight: bold;
        }

        table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 10px;
            vertical-align: top;
        }

        table th {
            border: 1px solid #000;
        }

        .text-center {
            text-align: center;
        }

        .sin-novedades {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 4px;
        }

        .firma {
            margin-top: 40px;
        }

        .firma-lugar {
            text-align: right;
            margin-bottom: 30px;
            margin-right: 20px;
            font-size: 11px;
        }

        .firma-contenido {
            text-align: center;
            width: 250px;
            margin: 0 auto;
        }

        .firma-contenido p {
            font-size: 11px;
        }

        .firma-linea {
            border-top: 1px solid #000;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>

    {{-- Encabezado --}}
    <div class="header text-left">
        <h2>{{ config('app.name') }}</h2>
        <h3>Novedades correspondientes al D.{{ strtoupper($guardia->date->format('dMy')) }}</h3>
    </div>

    @php
        $recibidos = $guardia->novedades->where('direction', 'Recibido');
        $expedidos = $guardia->novedades->where('direction', 'Expedido');

        $tipos = ['Radio', 'Fax', 'Correo Electrónico'];
        $labels = [
            'Radio' => ['recibido' => 'RADIOS RECIBIDOS', 'expedido' => 'RADIOS EXPEDIDOS'],
            'Fax' => ['recibido' => 'FAXES RECIBIDOS', 'expedido' => 'FAXES EXPEDIDOS'],
            'Correo Electrónico' => ['recibido' => 'CC.EE. RECIBIDOS', 'expedido' => 'CC.EE. EXPEDIDOS'],
        ];
        $colNum = [
            'Radio' => 'Nº RADIO',
            'Fax' => 'Nº FAX',
            'Correo Electrónico' => 'Nº C.E.',
        ];
    @endphp

    {{-- Recibidos por tipo --}}
    @foreach ($tipos as $tipo)
        @php $items = $recibidos->where('type', $tipo); @endphp
        <div class="seccion">
            <p class="seccion-titulo">Relación de {{ $labels[$tipo]['recibido'] }}:</p>
            <table>
                <thead>
                    <tr>
                        <th style="width:4%">Nº</th>
                        <th style="width:8%">Hora</th>
                        <th style="width:12%">{{ $colNum[$tipo] }}</th>
                        <th style="width:52%">Texto</th>
                        <th style="width:24%">Origen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items->values() as $i => $novedad)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}.</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($novedad->time)->format('Hi') }}</td>
                            <td class="text-center">{{ $novedad->number }}</td>
                            <td>{{ $novedad->text }}</td>
                            <td>{{ $novedad->organismo->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sin-novedades">S/N.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    {{-- Expedidos por tipo --}}
    @foreach ($tipos as $tipo)
        @php $items = $expedidos->where('type', $tipo); @endphp
        <div class="seccion">
            <p class="seccion-titulo">Relación de {{ $labels[$tipo]['expedido'] }}:</p>
            <table>
                <thead>
                    <tr>
                        <th style="width:4%">Nº</th>
                        <th style="width:8%">Hora</th>
                        <th style="width:12%">{{ $colNum[$tipo] }}</th>
                        <th style="width:24%">Destino</th>
                        <th style="width:52%">Texto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items->values() as $i => $novedad)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}.</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($novedad->time)->format('Hi') }}</td>
                            <td class="text-center">{{ $novedad->number }}</td>
                            <td>{{ $novedad->destino ?? '-' }}</td>
                            <td>{{ $novedad->text }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sin-novedades">S/N.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
    {{-- Novedades de Personal --}}
    @php $novedadesPersonal = $guardia->novedadesPersonal->sortBy('hora'); @endphp
    <div class="seccion">
        <p class="seccion-titulo">Novedades de Personal.</p>
        <table>
            <thead>
                <tr>
                    <th style="width:8%">Hora</th>
                    <th style="width:18%">Tipo</th>
                    <th style="width:74%">Texto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($novedadesPersonal as $item)
                    <tr>
                        <td class="text-center">{{ $item->hora->format('Hi') }}</td>
                        <td class="text-center">{{ $item->tipo }}</td>
                        <td>{{ $item->texto }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="sin-novedades">S/N.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Novedades de Rancho --}}
    @php
        $rancho = $guardia->novedadesRancho->sortBy('unidad.nombre');
        $conMenu = $rancho->filter(fn($r) => filled($r->menu));
    @endphp
    <div class="seccion">
        <p class="seccion-titulo">Novedades de Rancho.</p>
        <table>
            <thead>
                <tr>
                    <th style="width:22%">Unidad</th>
                    <th style="width:15%">Desayuno</th>
                    <th style="width:15%">Almuerzo</th>
                    <th style="width:15%">Merienda</th>
                    <th style="width:15%">Cena</th>
                    <th style="width:18%">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rancho as $item)
                    <tr>
                        <td>{{ $item->unidad->nombre }}</td>
                        <td class="text-center">{{ $item->desayuno ?? '-' }}</td>
                        <td class="text-center">{{ $item->almuerzo ?? '-' }}</td>
                        <td class="text-center">{{ $item->merienda ?? '-' }}</td>
                        <td class="text-center">{{ $item->cena ?? '-' }}</td>
                        <td class="text-center" style="font-weight:bold;">{{ $item->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="sin-novedades">S/N.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($guardia->ranchoMenu)
            <p style="margin-left:20px; margin-top:2px; font-size:10px;">
                @foreach (['menu_desayuno' => 'Desayuno', 'menu_almuerzo' => 'Almuerzo', 'menu_merienda' => 'Merienda', 'menu_cena' => 'Cena'] as $campo => $label)
                    @if ($guardia->ranchoMenu->{$campo})
                        <strong>{{ $label }}:</strong> {{ $guardia->ranchoMenu->{$campo} }}&nbsp;&nbsp;
                    @endif
                @endforeach
            </p>
        @endif
    </div>
    {{-- Salidas de Vehículos --}}
    @php
        $todasSalidas = $guardia->salidasVehiculos;
        $tiposCombustible = [
            'gas_oil' => 'GAS OIL',
            'nafta' => 'NAFTA',
        ];
    @endphp

    @foreach ($tiposCombustible as $tipo => $label)
        @php
            $salidas = $todasSalidas->where('tipo_combustible', $tipo)->values();
            $totalKms = $salidas->sum('kms_recorridos');
            $totalLts = $salidas->sum('litros');
        @endphp
        <div class="seccion">
            <p class="seccion-titulo">Relación de Salidas de Vehículos a {{ $label }}.:</p>
            <table>
                <thead>
                    <tr>
                        <th style="width:7%">Hora Sale</th>
                        <th style="width:7%">Hora Entra</th>
                        <th style="width:8%">Kms. Sale</th>
                        <th style="width:8%">Kms. Entra</th>
                        <th style="width:8%">Kms Rec.</th>
                        <th style="width:9%">Lts. {{ $label }}</th>
                        <th style="width:9%">Mat.</th>
                        <th style="width:20%">Conductor</th>
                        <th style="width:24%">Comisión</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salidas as $salida)
                        <tr>
                            <td class="text-center">{{ optional($salida->hora_sale)->format('Hi') }}</td>
                            <td class="text-center">{{ optional($salida->hora_entra)->format('Hi') ?? '-' }}</td>
                            <td class="text-center">{{ $salida->kms_sale ?? '-' }}</td>
                            <td class="text-center">{{ $salida->kms_entra ?? '-' }}</td>
                            <td class="text-center">{{ $salida->kms_recorridos ?? '-' }}</td>
                            <td class="text-center">
                                {{ $salida->litros !== null ? number_format($salida->litros, 2) : '-' }}</td>
                            <td class="text-center">{{ $salida->vehiculo->matricula ?? 'S/D' }}</td>
                            <td>{{ $salida->conductor->nombre_visible ?? 'S/D' }}</td>
                            <td>{{ $salida->comision }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="sin-novedades">S/N.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <p style="text-align: right; margin-right: 20px; margin-top: 2px; font-weight: bold; font-size: 11px;">
                TOTAL KMS RECORRIDOS: {{ $totalKms }} Kms &nbsp;&nbsp; TOTAL LTS COMBUSTIBLE:
                {{ number_format($totalLts, 2) }} Lts.
            </p>
        </div>
    @endforeach

    {{-- ============================================================ --}}
    {{-- SECCIÓN PALOMAR (total de todas las palomas del sistema)     --}}
    {{-- ============================================================ --}}
    @php
        use App\Models\Paloma;

        $todasLasPalomas = Paloma::all();
        $total = $todasLasPalomas->count();
        $adultas = $todasLasPalomas->filter(fn($p) => !$p->es_pichon)->count();
        $pichones = $todasLasPalomas->filter(fn($p) => $p->es_pichon)->count();
        $reproductoras = $todasLasPalomas->filter(fn($p) => optional($p->estado)->nombre === 'Reproductora')->count();
        $ausentes = $todasLasPalomas->filter(fn($p) => optional($p->estado)->nombre === 'Ausente')->count();
        $presentes = $total - $ausentes;

        // Estado sanitario: tomamos el valor de la primera paloma que tenga definido el campo,
        // o 'S/N' si no hay o está vacío.
        $estadoSanitario = 'S/N';
        $primeraConEstado = $todasLasPalomas->firstWhere('estado_sanitario', '!=', null);
        if ($primeraConEstado) {
            $estadoSanitario = $primeraConEstado->estado_sanitario;
        }
    @endphp

    @if ($total > 0)
        <div class="seccion">
            <p class="seccion-titulo">NOVEDADES DE PALOMAR MILITAR.</p>
            <table>
                <thead>
                    <tr>
                        <th>EXISTENCIA</th>
                        <th>PALOMAS ADULTAS</th>
                        <th>PICHONES</th>
                        <th>REPRODUCTORAS</th>
                        <th>AUSENTES</th>
                        <th>PRESENTES</th>
                        <th>ESTADO SANITARIO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">{{ $total }}</td>
                        <td class="text-center">{{ $adultas }}</td>
                        <td class="text-center">{{ $pichones }}</td>
                        <td class="text-center">{{ $reproductoras }}</td>
                        <td class="text-center">{{ $ausentes }}</td>
                        <td class="text-center">{{ $presentes }}</td>
                        <td class="text-center">{{ $estadoSanitario }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    {{-- Firma --}}
    <div style="margin-top: 40px; font-size: 11px;">

        <p style="text-align: right; margin-right: 60px;">
            Cuartel en Peñarol,
            {{ $guardia->date->format('d') }}0830{{ strtoupper($guardia->date->format('My')) }}.<br>

        </p>
        <p style="text-align: right; margin-right: 60px;">
            El Ofl. de Día de la {{ config('organizacion.nombre') }}
        </p>
        <table style="width: 100%; border-collapse: collapse; " border="0">
            <tr>
                <td style="width: 25%; font-style: italic; vertical-align: bottom; text-align: right; border: none;">
                    @if ($guardia->escribiente->first())
                        {{ strtoupper(substr($guardia->escribiente->first()->name, 0, 1)) }}{{ strtoupper(substr($guardia->escribiente->first()->last_name, 0, 1)) }}.
                    @endif
                </td>
                <td style="width: 50%; text-align: center;border: none;">
                </td>
                <td style="width: 25%;border: none;">
                    {{ strtoupper($guardia->oficial->grade) }}<br>
                    <div style="border-top: 1px solid #000; width: 150px; margin-bottom: 3px;"></div>
                    <p style="text-align: center; font-size: 11px;">
                        {{ strtoupper($guardia->oficial->name) }} {{ strtoupper($guardia->oficial->last_name) }}.
                    </p>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>