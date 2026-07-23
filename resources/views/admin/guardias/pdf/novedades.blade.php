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

        @page {
            margin: 60px 20px 40px 20px;
            /* arriba, derecha, abajo, izquierda — ajustá según lo que ya tenías */
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

        .firma-tabla {
            page-break-inside: avoid;
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
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
        // Salidas que se originaron en esta guardia
        $misSalidas = $guardia->salidasVehiculos()
            ->with(['vehiculo', 'conductor', 'boletaCierre'])
            ->get();

        // Salidas originadas en OTRA guardia pero cuya boleta de cierre
        // (regreso del vehículo) se registró en esta guardia
        $retornosDeOtrasGuardias = \App\Models\SalidaVehiculo::whereHas('boletaCierre', function ($q) use ($guardia) {
                $q->where('guardia_id', $guardia->id);
            })
            ->where('guardia_id', '!=', $guardia->id)
            ->with(['vehiculo', 'conductor', 'guardia', 'boletaCierre'])
            ->get();

        $todasSalidas = $misSalidas->concat($retornosDeOtrasGuardias);

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
                        <th style="width:5%">Hora Sale</th>
                        <th style="width:5%">Fecha Sale</th>
                        <th style="width:4%">Hora Entra</th>
                        <th style="width:4%">Fecha Entra</th>
                        <th style="width:8%">Estado</th>
                        <th style="width:7%">Kms. Sale</th>
                        <th style="width:7%">Kms. Entra</th>
                        <th style="width:7%">Kms Rec.</th>
                        <th style="width:9%">Lts. {{ $label }}</th>
                        <th style="width:9%">Mat.</th>
                        <th style="width:21%">Conductor</th>
                        <th style="width:14%">Comisión</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salidas as $salida)
                        <tr>
                            <td class="text-center">{{ optional($salida->hora_sale)->format('Hi') }}</td>
                            <td class="text-center">{{ $salida->guardia->date->format('d/m') }}</td>
                            <td class="text-center">
                                @if ($salida->boletaCierre)
                                    {{ optional($salida->boletaCierre->hora_entra)->format('Hi') }}
                                @elseif ($salida->hora_entra)
                                    {{ optional($salida->hora_entra)->format('Hi') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($salida->boletaCierre)
                                    {{ optional($salida->boletaCierre->fecha_entra)->format('d/m') }}
                                @elseif ($salida->hora_entra)
                                    {{ $salida->guardia->date->format('d/m') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($salida->boletaCierre)
                                    <span style="color:green; font-weight:bold;">CERRADA</span><br><small></small>
                                @elseif ($salida->hora_entra && $salida->kms_entra)
                                    <span style="color:green; font-weight:bold;">CERRADA</span>
                                @else
                                    <span style="color:red; font-weight:bold;">PENDIENTE</span>
                                @endif
                            </td>
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
                            <td colspan="12" class="sin-novedades">S/N.</td>
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
        $todasLasPalomas = \App\Models\Paloma::with('estado')->get();
        $total = $todasLasPalomas->count();
        $adultas = $todasLasPalomas->filter(fn($p) => !$p->es_pichon)->count();
        $pichones = $todasLasPalomas->filter(fn($p) => $p->es_pichon)->count();
        $reproductoras = $todasLasPalomas->filter(fn($p) => optional($p->estado)->nombre === 'Reproductora')->count();
        $ausentes = $todasLasPalomas->filter(fn($p) => optional($p->estado)->nombre === 'Ausente')->count();
        $presentes = $total - $ausentes;

        // Estado sanitario: conteo real de palomas en cada estado.
        $palomasSanas = $todasLasPalomas->where('estado_sanitario', 'Bien')->count();
        $palomasEnfermas = $todasLasPalomas->where('estado_sanitario', 'Enferma')->count();
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
                        <th>SANAS</th>
                        <th>ENFERMAS</th>
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
                        <td class="text-center">{{ $palomasSanas }}</td>
                        <td class="text-center">{{ $palomasEnfermas }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    {{-- Firma --}}
    <div style="margin-top: 40px; font-size: 11px;">

        <table class="firma-tabla" style="width: 100%; border-collapse: collapse; page-break-inside: avoid;"
            border="0">
            <tr>
                <td colspan="3" style="text-align: right; padding-right: 60px; border: none; font-size: 11px;">
                    Cuartel en Peñarol,
                    {{ $guardia->date->format('d') }}0830{{ strtoupper($guardia->date->format('My')) }}.
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right; padding-right: 60px; border: none; font-size: 11px;">
                    El Ofl. de Día de la {{ config('organizacion.nombre') }}
                </td>
            </tr>
            
            <tr>
                <td style="width: 25%; font-style: italic; vertical-align: bottom; text-align: right; border: none;">
                    @if ($guardia->escribiente->first())
                        {{ strtoupper(substr($guardia->escribiente->first()->name, 0, 1)) }}{{ strtoupper(substr($guardia->escribiente->first()->last_name, 0, 1)) }}.
                    @endif
                </td>
                <td style="width: 50%; border: none;"></td>
                <td colspan="3" style="text-align: left; padding-left: 35px; border: none; font-size: 11px;">
                    {{ strtoupper($guardia->oficial->grade) }}<br>
                    <div style="border-top: 1px solid #000; width: 150px;  margin-bottom: 3px;"></div>
                    <p style="text-align: center; font-size: 11px;">
                        {{ strtoupper($guardia->oficial->name) }} {{ strtoupper($guardia->oficial->last_name) }}.
                    </p>
                </td>
            </tr>
        </table>

        {{-- Pie con jerarquías de la guardia --}}
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid;" border="0">
            <tr>
                <td style="border: none; font-size: 11px; padding: 2px 20px;">
                    <strong>Capitán de Servicio:</strong>
                    {{ strtoupper($guardia->capitan->grade) }} {{ strtoupper($guardia->capitan->name) }} {{ strtoupper($guardia->capitan->last_name) }}
                </td>
            </tr>
            <tr>
                <td style="border: none; font-size: 11px; padding: 2px 20px;">
                    <strong>Oficial de Día:</strong>
                    {{ strtoupper($guardia->oficial->grade) }} {{ strtoupper($guardia->oficial->name) }} {{ strtoupper($guardia->oficial->last_name) }}
                </td>
            </tr>
            <tr>
                <td style="border: none; font-size: 11px; padding: 2px 20px;">
                    <strong>Escribiente de Servicio:</strong>
                    @if ($guardia->escribiente->first())
                        {{ strtoupper($guardia->escribiente->first()->grade) }} {{ strtoupper($guardia->escribiente->first()->name) }} {{ strtoupper($guardia->escribiente->first()->last_name) }}
                    @else
                        S/D
                    @endif
                </td>
            </tr>
        </table>
    </div>
    @if ($incluirAdjuntos ?? false)
        @include('admin.guardias.pdf.anexos-recibidos', ['guardia' => $guardia])
    @endif

</body>

</html>