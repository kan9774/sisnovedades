@extends('layouts.app')


@section('subtitle', 'Palomar: ' . $palomar->nombre)
@section('content_header_title', 'Palomares')
@section('content_header_subtitle', $palomar->nombre)

@section('content_body')
    <div class="container-fluid">
        <div class="row">
            {{-- Resumen del palomar (sin cambios) --}}
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="card card-outline card-primary">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #0d6efd;">
                        <h5 class="mb-0"><i class="fas fa-chart-pie text-primary"></i> Resumen del Palomar</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $total = $palomar->palomas->count();
                            $adultas = $palomar->palomas->filter(fn($p) => !$p->es_pichon)->count();
                            $pichones = $palomar->palomas->filter(fn($p) => $p->es_pichon)->count();
                            $reproductoras = $palomar->palomas
                                ->filter(fn($p) => optional($p->estado)->nombre === 'Reproductora')
                                ->count();
                            $ausentes = $palomar->palomas
                                ->filter(fn($p) => optional($p->estado)->nombre === 'Ausente')
                                ->count();
                            $bajas = $palomar->palomas
                                ->filter(fn($p) => optional($p->estado)->nombre === 'Baja')
                                ->count();
                        @endphp
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-dove mr-2"></i> Total existencias</span>
                                <span class="badge bg-primary rounded-pill">{{ $total }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user-check mr-2"></i> Adultas</span>
                                <span class="badge bg-success rounded-pill">{{ $adultas }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-baby mr-2"></i> Pichones</span>
                                <span class="badge bg-info rounded-pill">{{ $pichones }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-heart mr-2"></i> Reproductoras</span>
                                <span class="badge bg-warning text-dark rounded-pill">{{ $reproductoras }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-plane-departure mr-2"></i> Ausentes</span>
                                <span class="badge bg-secondary rounded-pill">{{ $ausentes }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-skull mr-2"></i> Bajas</span>
                                <span class="badge bg-danger rounded-pill">{{ $bajas }}</span>
                            </li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('admin.palomares.reporte', $palomar) }}" class="btn btn-outline-danger btn-sm"
                                style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                target="_blank">
                                <i class="fas fa-file-pdf"></i> Generar Parte Diario
                            </a>
                            <a href="{{ route('admin.palomares.index') }}" class="btn btn-outline-secondary btn-sm"
                                style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Listado de palomas con DataTable --}}
            <div class="col-lg-8 col-md-7">
                <div class="card card-outline card-success">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #28a745;">
                        <h5 class="card-title">
                            <i class="fas fa-dove text-success"></i> Palomas del Palomar
                            <span class="badge bg-success ml-2">{{ $palomar->palomas->count() }}</span>
                        </h5>
                        <div class="card-tools">
                            <a href="{{ route('admin.palomas.create', ['palomar_id' => $palomar->id]) }}"
                                class="btn btn-outline-primary btn-sm mt-2 mt-sm-0"
                                style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                                <i class="fas fa-plus-circle"></i> Agregar Paloma
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tabla-palomas" class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Anilla</th>
                                        <th>Nombre</th>
                                        <th>Sexo</th>
                                        <th>Estado</th>
                                        <th>Edad</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($palomar->palomas as $paloma)
                                        <tr>
                                            <td><strong>{{ $paloma->anilla }}</strong></td>
                                            <td>{{ $paloma->nombre ?? '-' }}</td>
                                            <td>
                                                @if ($paloma->sexo === 'macho')
                                                    <span class="badge bg-primary"><i class="fas fa-mars mr-1"></i>
                                                        Macho</span>
                                                @elseif($paloma->sexo === 'hembra')
                                                    <span class="badge bg-danger"><i class="fas fa-venus mr-1"></i>
                                                        Hembra</span>
                                                @else
                                                    <span class="badge bg-secondary">Desconocido</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($paloma->estado)
                                                    <span class="badge"
                                                        style="background-color: {{ $paloma->estado->color ?? '#6c757d' }}; color: #fff; padding: 5px 12px; font-weight: 500;">
                                                        <i class="fas fa-circle mr-1" style="font-size: 0.5rem;"></i>
                                                        {{ $paloma->estado->nombre }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Sin estado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($paloma->fecha_nacimiento)
                                                    <span class="badge bg-light text-dark">
                                                        <i class="far fa-clock mr-1"></i>
                                                        {{ intval($paloma->fecha_nacimiento->diffInMonths(now())) }} meses
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                                    <a href="{{ route('admin.palomas.show', $paloma) }}"
                                                        class="btn btn-outline-info btn-xs mr-1"
                                                        style="background-color: rgba(23, 162, 184, 0.08); border-color: rgba(23, 162, 184, 0.25);">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.palomas.edit', $paloma) }}"
                                                        class="btn btn-outline-warning btn-xs mr-1"
                                                        style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-dove fa-2x d-block mb-2" style="opacity: 0.3;"></i>
                                                No hay palomas en este palomar.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#tabla-palomas').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Todos"]
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "columnDefs": [{
                        "orderable": false,
                        "targets": -1
                    } // Desactivar orden en columna Acciones
                ],
                "responsive": true,
                "autoWidth": false
            });

            // Auto-ocultar alertas después de 5 segundos
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
@endpush

@push('css')
    <style>
        /* =============================================
           REDONDEAR BORDES DE LA TABLA (SOLO LA TABLA)
           ============================================= */

        #tabla-palomas {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            /* redondeo general */
            overflow: hidden;
            /* para que las esquinas respeten el borde */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        /* Encabezado: esquinas superiores redondeadas */
        #tabla-palomas thead th:first-child {
            border-top-left-radius: 12px;
        }

        #tabla-palomas thead th:last-child {
            border-top-right-radius: 12px;
        }

        /* Última fila: esquinas inferiores redondeadas */
        #tabla-palomas tbody tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }

        #tabla-palomas tbody tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }

        /* Opcional: bordes suaves y hover */
        #tabla-palomas th,
        #tabla-palomas td {
            border-color: #e9ecef;
        }

        #tabla-palomas thead th {
            background-color: #f8f9fa !important;
            color: #495057;
            font-weight: 600;
            padding: 12px 15px;
            border-bottom: 2px solid #dee2e6;
        }

        #tabla-palomas tbody td {
            padding: 10px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }

        #tabla-palomas tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Mejora en los controles de DataTable (opcional) */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px !important;
            border: 1px solid #ced4da !important;
            padding: 5px 15px !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 4px !important;
            border: 1px solid #ced4da !important;
        }

        .card-outline {
            border-radius: 15px !important;
        }

        .card-outline .card-header {
            border-radius: 15px 15px 0 0 !important;
        }

        .list-group-item {
            border: none !important;
            padding: 10px 0 !important;
            border-bottom: 1px solid #f0f0f0 !important;
        }

        .list-group-item:last-child {
            border-bottom: none !important;
        }

        .badge {
            font-weight: 500 !important;
        }

        .btn-outline-info,
        .btn-outline-warning,
        .btn-outline-primary,
        .btn-outline-secondary,
        .btn-outline-danger {
            border-radius: 50px !important;
            transition: all 0.3s ease !important;
            font-weight: 600 !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-info:hover,
        .btn-outline-warning:hover,
        .btn-outline-secondary:hover,
        .btn-outline-danger:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-xs {
            padding: 0.1rem 0.4rem !important;
            font-size: 0.7rem !important;
            line-height: 1.5 !important;
            border-radius: 50px !important;
        }

        /* DataTable: ajustes de espaciado */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            padding: 10px 15px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 10px 15px;
        }

        @media (max-width: 576px) {
            .table-responsive table {
                font-size: 0.8rem !important;
            }

            .card-header .btn {
                font-size: 0.7rem !important;
                padding: 4px 10px !important;
            }

            .card-header h5 {
                font-size: 1rem !important;
            }

            .list-group-item {
                font-size: 0.9rem !important;
            }
        }
    </style>
@endpush
