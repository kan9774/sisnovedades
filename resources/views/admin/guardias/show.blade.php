@extends('layouts.app')

@section('plugins.Datatables', true)

@section('subtitle', 'Guardia ' . $guardia->date->format('d/m/Y'))
@section('content_header_title', 'Guardias')
@section('content_header_subtitle', $guardia->date->format('d/m/Y'))

@section('content_body')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        {{-- Info de la guardia --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i>
                    Guardia del {{ $guardia->date->format('d/m/Y') }}
                    @if ($guardia->status === 'open')
                        <span class="badge badge-success ml-3">Abierta</span>
                    @else
                        <span class="badge badge-secondary ml-3">Cerrada</span>
                    @endif
                </h3>
                <div class="card-tools">
                    <div class="d-flex align-items-center">

                        {{-- Botón Cerrar Guardia --}}
                        @can('cerrar', $guardia)
                            <form action="{{ route('admin.guardias.cerrar', $guardia) }}" method="POST" class="d-inline ml-1">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm" data-toggle="tooltip" title="Cerrar guardia"
                                    onclick="return confirm('¿Cerrar la guardia?')">
                                    <i class="fas fa-lock"></i>
                                </button>
                            </form>
                        @endcan

                        {{-- Botón Reactivar Guardia --}}
                        @can('reactivar', $guardia)
                            <form action="{{ route('admin.guardias.reactivar', $guardia) }}" method="POST"
                                class="d-inline ml-1">
                                @csrf
                                <button class="btn btn-outline-warning btn-sm" data-toggle="tooltip" title="Reactivar guardia"
                                    onclick="return confirm('¿Reactivar la guardia?')">
                                    <i class="fas fa-lock-open"></i>
                                </button>
                            </form>
                        @endcan

                        {{-- Botón Eliminar Guardia (solo Super Admin) --}}
                        @can('delete', $guardia)
                            <form action="{{ route('admin.guardias.destroy', $guardia) }}" method="POST" class="d-inline ml-1">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" data-toggle="tooltip" title="Mover a papelera"
                                    onclick="return confirm('¿Eliminar la guardia del {{ $guardia->date->format('d/m/Y') }}?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endcan
                        {{-- Botón Volver --}}
                        <a href="{{ route('admin.guardias.index') }}" class="btn btn-outline-secondary btn-sm ml-1"
                            data-toggle="tooltip" title="Volver">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Capitán de Servicio:</strong><br>
                        {{ $guardia->capitan->grade }} {{ $guardia->capitan->name }} {{ $guardia->capitan->last_name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Oficial de Día:</strong><br>
                        {{ $guardia->oficial->grade }} {{ $guardia->oficial->name }}
                        {{ $guardia->oficial->last_name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Escribientes:</strong><br>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @forelse($guardia->escribiente as $escribiente)
                                <span class="d-inline-flex align-items-center px-3 py-1 rounded border"
                                    style="background-color: rgba(0, 123, 255, 0.08); 
                         border-color: rgba(0, 123, 255, 0.25); 
                         color: #007bff;
                         font-size: 0.875rem;">
                                    {{ $escribiente->grade }} {{ $escribiente->name }} {{ $escribiente->last_name }}
                                </span>
                            @empty
                                <span class="text-muted">Sin escribientes</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-3">
                        <strong>Imprimir Guardia:</strong><br>
                        <a href="{{ route('admin.guardias.pdf', $guardia) }}"
                            class="btn btn-outline-danger btn-ml ml-1 align-items-center" data-toggle="tooltip"
                            title="Imprimir Guardia" target="_blank">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </div>
                </div>
                @if ($guardia->notes)
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Notas:</strong> {{ $guardia->notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Novedades --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-tower-cell"></i> Novedades
                    <span class="badge badge-primary ml-2">{{ $guardia->novedades->count() }}</span>
                </h3>
                <div class="card-tools">
                    {{-- Botón Crear Novedad --}}
                    @can('create', App\Models\News::class)
                        @if ($guardia->status === 'open')
                            <a href="{{ route('admin.guardias.novedades.create', $guardia) }}"
                                class="btn btn-outline-info btn-sm ml-1" data-toggle="tooltip" title="Crear novedad">
                                <i class="fas fa-plus-circle"></i> Registrar Tráfico
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <table id="tabla-novedades" class="table table-striped table-hover mb-0" style="width: 100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Dirección</th>
                            <th>Número</th>
                            <th>Asunto</th>
                            <th>Clasificación</th>
                            <th>Escribiente</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guardia->novedades as $novedad)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $novedad->time }}</td>
                                <td>{{ $novedad->type }}</td>
                                <td>
                                    @if ($novedad->direction === 'Recibido')
                                        <span class="badge badge-success">Recibido</span>
                                    @else
                                        <span class="badge badge-warning">Expedido</span>
                                    @endif
                                </td>
                                <td>{{ $novedad->number }}</td>
                                <td>{{ Str::limit($novedad->affair, 40) }}</td>
                                <td>
                                    @php
                                        $colores = [
                                            'Rutinario' => 'secondary',
                                            'Prioritario' => 'primary',
                                            'Urgente' => 'warning',
                                            'Destello' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $colores[$novedad->clasification] ?? 'secondary' }}">
                                        {{ $novedad->clasification }}
                                    </span>
                                </td>
                                <td>{{ $novedad->escribiente->name ?? '-' }}</td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        <!-- Botón Ver -->
                                        <a href="{{ route('admin.guardias.novedades.show', [$guardia, $novedad]) }}"
                                            class="btn btn-outline-info btn-xs mr-1"
                                            style="background-color: rgba(23, 162, 184, 0.1);" aria-label="Ver novedad">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @can('update', $novedad)
                                            <!-- Botón Editar -->
                                            <a href="{{ route('admin.guardias.novedades.edit', [$guardia, $novedad]) }}"
                                                class="btn btn-outline-warning btn-xs mr-1"
                                                style="background-color: rgba(255, 193, 7, 0.1);" aria-label="Editar novedad">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        @can('delete', $novedad)
                                            <!-- Botón Eliminar -->
                                            <form
                                                action="{{ route('admin.guardias.novedades.destroy', [$guardia, $novedad]) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar novedad?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.1);"
                                                    aria-label="Eliminar novedad">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-tower-cell fa-2x d-block mb-2"></i>
                                No hay tráficos registrados en esta guardia.
                                @can('create', App\Models\Novedad::class)
                                    @if ($guardia->status === 'open')
                                        <br>
                                        <a href="{{ route('admin.guardias.novedades.create', $guardia) }}"
                                            class="btn btn-outline-primary btn-sm mt-2"
                                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                                            <i class="fas fa-plus-circle"></i> Registrar el primer tráfico
                                        </a>
                                    @endif
                                @endcan
                            </div>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @php
            $puedeRegistrarSalida =
                $guardia->captain_id === auth()->id() ||
                $guardia->oficer_id === auth()->id() ||
                $guardia->escribiente->contains('id', auth()->id()) ||
                auth()->user()->isAdmin();
        @endphp
        {{-- SALIDAS DE VEHÍCULOS (independientes de novedades) --}}
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck"></i> Salidas de Vehículos
                    <span class="badge badge-primary ml-2">{{ $guardia->salidasVehiculos->count() }}</span>
                </h3>
                <div class="card-tools">
                    @can('create', App\Models\SalidaVehiculo::class)
                        @if ($guardia->status === 'open' && $puedeRegistrarSalida)
                            <a href="{{ route('admin.guardias.salidas.create', $guardia) }}"
                                class="btn btn-outline-info btn-sm ml-1" aria-label="Registrar salida de vehículo">
                                <i class="fas fa-plus-circle"></i> Registrar Salida
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @if ($guardia->salidasVehiculos->count() > 0)
                    <table id="tabla-salidas" class="table table-striped table-hover mb-0" style="width: 100%">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Vehículo</th>
                                <th>Conductor</th>
                                <th>Combustible</th>
                                <th>Hora Sale</th>
                                <th>Hora Entra</th>
                                <th>Km</th>
                                <th>Litros</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guardia->salidasVehiculos as $index => $salida)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if ($salida->vehiculo)
                                            <strong>{{ $salida->vehiculo->matricula }}</strong>
                                            @if ($salida->vehiculo->sin_cuentakilometros)
                                                <span class="badge badge-danger badge-pill">S/C</span>
                                            @endif
                                        @else
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Vehículo eliminado
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($salida->conductor)
                                            {{ $salida->conductor->nombre_visible }}
                                        @else
                                            <span class="text-danger">Conductor eliminado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($salida->tipo_combustible === 'gas_oil')
                                            <span class="badge badge-warning">Gas Oil</span>
                                        @elseif($salida->tipo_combustible === 'nafta')
                                            <span class="badge badge-info">Nafta</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $salida->tipo_combustible }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $salida->hora_sale?->format('H:i') }}</td>
                                    <td>{{ $salida->hora_entra?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $salida->kms_recorridos ?? '-' }}</td>
                                    <td>{{ $salida->litros ? number_format($salida->litros, 2) : '-' }}</td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center">
                                            @can('update', $salida)
                                                <a href="{{ route('admin.guardias.salidas.edit', [$guardia, $salida]) }}"
                                                    class="btn btn-outline-warning btn-xs mr-1"
                                                    style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                                    aria-label="Editar salida">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $salida)
                                                <form
                                                    action="{{ route('admin.guardias.salidas.destroy', [$guardia, $salida]) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('¿Eliminar esta salida?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-xs"
                                                        style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                        aria-label="Eliminar salida">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($guardia->salidasVehiculos->count() > 0)
                            <tfoot>
                                @php
                                    $resumenCombustible = $guardia->salidasVehiculos
                                        ->groupBy('tipo_combustible')
                                        ->map(function ($grupo) {
                                            return [
                                                'tipo_combustible' => $grupo->first()->tipo_combustible,
                                                'total_kms' => $grupo->sum('kms_recorridos'),
                                                'total_litros' => $grupo->sum('litros'),
                                            ];
                                        });
                                @endphp
                                @foreach ($resumenCombustible as $resumen)
                                    <tr class="font-weight-bold" style="background: #f8f9fa;">
                                        <td colspan="6" class="text-right">
                                            TOTAL
                                            @if ($resumen['tipo_combustible'] === 'gas_oil')
                                                <span class="badge badge-warning">Gas Oil</span>
                                            @elseif ($resumen['tipo_combustible'] === 'nafta')
                                                <span class="badge badge-info">Nafta</span>
                                            @else
                                                <span
                                                    class="badge badge-secondary">{{ $resumen['tipo_combustible'] }}</span>
                                            @endif
                                            :
                                        </td>
                                        <td>{{ $resumen['total_kms'] ?? 0 }}</td>
                                        <td>{{ number_format($resumen['total_litros'] ?? 0, 2) }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            </tfoot>
                        @endif
                    </table>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-truck fa-2x d-block mb-2"></i>
                        No hay salidas de vehículos registradas en esta guardia.
                        @can('create', App\Models\SalidaVehiculo::class)
                            @if ($guardia->status === 'open' && $puedeRegistrarSalida)
                                <br>
                                <a href="{{ route('admin.guardias.salidas.create', $guardia) }}"
                                    class="btn btn-outline-primary btn-sm mt-2"
                                    style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                                    <i class="fas fa-plus-circle"></i> Registrar la primera salida
                                </a>
                            @endif
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        $(document).ready(function() {
            $('.alert').delay(3000).fadeOut('slow');

            const idiomaEs = {
                emptyTable: "No hay datos disponibles",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                lengthMenu: "Mostrar _MENU_ registros",
                search: "Buscar:",
                zeroRecords: "No se encontraron resultados",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            };

            // Tabla de Novedades
            $('#tabla-novedades').DataTable({
                pageLength: 10,
                lengthChange: false,
                language: idiomaEs,
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }]
            });

            // Tabla de Salidas de Vehículos (única)
            $('#tabla-salidas').DataTable({
                pageLength: 10,
                lengthChange: false,
                language: idiomaEs,
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }]
            });
        });
    </script>
@endpush
