@extends('layouts.app')


@section('subtitle', 'Palomas')
@section('content_header_title', 'Palomas')
@section('content_header_subtitle', 'Listado general')

@section('content_body')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-dove"></i> Todas las palomas</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.palomas.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Nueva Paloma
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-palomas" class="table table-striped table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Anilla</th>
                                <th>Nombre</th>
                                <th>Palomar</th>
                                <th>Sexo</th>
                                <th>Edad (meses)</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($palomas as $paloma)
                                <tr>
                                    <td><strong>{{ $paloma->anilla }}</strong></td>
                                    <td>{{ $paloma->nombre ?? '-' }}</td>
                                    <td>{{ $paloma->palomar->nombre ?? '-' }}</td>
                                    <td>
                                        @if ($paloma->sexo === 'macho')
                                            <span class="badge bg-primary"><i class="fas fa-mars mr-1"></i> Macho</span>
                                        @elseif($paloma->sexo === 'hembra')
                                            <span class="badge bg-danger"><i class="fas fa-venus mr-1"></i> Hembra</span>
                                        @else
                                            <span class="badge bg-secondary">Desconocido</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($paloma->fecha_nacimiento)
                                            @php
                                                $meses = intval($paloma->fecha_nacimiento->diffInMonths(now()));
                                            @endphp
                                            {{ $meses }}
                                        @else
                                            -
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
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.palomas.show', $paloma) }}"
                                                class="btn btn-outline-info btn-xs"
                                                style="background-color: rgba(23, 162, 184, 0.08); border-color: rgba(23, 162, 184, 0.25);"
                                                aria-label="Ver paloma">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.palomas.edit', $paloma) }}"
                                                class="btn btn-outline-warning btn-xs"
                                                style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                                aria-label="Editar paloma">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.palomas.destroy', $paloma) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('¿Eliminar esta paloma?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                    aria-label="Eliminar paloma">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-dove fa-2x d-block mb-2" style="opacity: 0.3;"></i>
                                        No hay palomas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        /* Ajustes de espaciado y bordes para DataTable */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            padding: 10px 15px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 10px 15px;
        }

        /* Redondear bordes de la tabla y sus celdas */
        #tabla-palomas {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
        }

        #tabla-palomas thead th:first-child {
            border-top-left-radius: 12px;
        }

        #tabla-palomas thead th:last-child {
            border-top-right-radius: 12px;
        }

        #tabla-palomas tbody tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }

        #tabla-palomas tbody tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }

        #tabla-palomas thead th {
            background-color: #f8f9fa !important;
            color: #495057;
            font-weight: 600;
            padding: 12px 15px;
            border-bottom: 2px solid #dee2e6 !important;
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

        /* Botones de acción más compactos */
        .btn-xs {
            padding: 0.1rem 0.4rem !important;
            font-size: 0.7rem !important;
            line-height: 1.5 !important;
            border-radius: 50px !important;
        }

        /* Estilo de filtros de DataTable */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px !important;
            border: 1px solid #ced4da !important;
            padding: 5px 15px !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 4px !important;
            border: 1px solid #ced4da !important;
            padding: 4px 8px !important;
        }
    </style>
@endpush

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
                    } // Desactivar orden en la columna de acciones
                ],
                "responsive": true,
                "autoWidth": false
            });

            // Auto-ocultar alertas después de 5 segundos
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
@endpush
