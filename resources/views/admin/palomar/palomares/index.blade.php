@extends('layouts.app')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('subtitle', 'Palomares')
@section('content_header_title', 'Palomares')
@section('content_header_subtitle', 'Listado')

@section('content_body')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline-ops">
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                <h3 class="card-title-ops mb-0"><i class="fas fa-dove"></i> Palomares registrados</h3>
                <span class="card-header-ops__eyebrow">{{ $palomares->count() }} registros</span>
            </div>
            <div class="card-tools">
                <a href="{{ route('admin.palomares.create') }}" class="btn-ops btn-ops-primary btn-sm">
                    <i class="fas fa-plus-circle"></i> Nuevo Palomar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-palomares" class="table table-striped table-hover table-bordered" style="width:100%">
                    <thead class="thead-ops">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Ubicación</th>
                            <th>Capacidad</th>
                            <th>Palomas</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($palomares as $palomar)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $palomar->nombre }}</strong></td>
                                <td>{{ $palomar->ubicacion ?? '-' }}</td>
                                <td>{{ $palomar->capacidad_maxima ?? '-' }}</td>
                                <td>{{ $palomar->palomas_count ?? $palomar->palomas->count() }}</td>
                                <td>
                                    @if($palomar->activo)
                                        <span class="badge-ops badge-ops-success"><i class="fas fa-check-circle mr-1"></i> Activo</span>
                                    @else
                                        <span class="badge-ops badge-ops-danger"><i class="fas fa-times-circle mr-1"></i> Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.palomares.show', $palomar) }}" 
                                           class="btn-ops btn-ops-info btn-xs"
                                           aria-label="Ver palomar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.palomares.edit', $palomar) }}" 
                                           class="btn-ops btn-ops-warning btn-xs"
                                           aria-label="Editar palomar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.palomares.destroy', $palomar) }}" 
                                              method="POST" 
                                              style="display:inline-block;" 
                                              onsubmit="return confirm('¿Eliminar este palomar?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-ops btn-ops-danger btn-xs"
                                                    aria-label="Eliminar palomar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-home fa-2x d-block mb-2" style="opacity: 0.3;"></i>
                                    No hay palomares registrados.
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
    /* Ajustes de espaciado para DataTable */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 10px 15px;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 10px 15px;
    }

    /* =============================================
       REDONDEAR BORDES DE LA TABLA (SOLO LA TABLA)
       ============================================= */
    #tabla-palomares {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
    }

    #tabla-palomares thead th:first-child {
        border-top-left-radius: 12px;
    }
    #tabla-palomares thead th:last-child {
        border-top-right-radius: 12px;
    }

    #tabla-palomares tbody tr:last-child td:first-child {
        border-bottom-left-radius: 12px;
    }
    #tabla-palomares tbody tr:last-child td:last-child {
        border-bottom-right-radius: 12px;
    }

    /* Estilo de encabezados */
    #tabla-palomares thead th {
        background-color: #f8f9fa !important;
        color: #495057;
        font-weight: 600;
        padding: 12px 15px;
        border-bottom: 2px solid #dee2e6 !important;
    }

    #tabla-palomares tbody td {
        padding: 10px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    #tabla-palomares tbody tr:hover {
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
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25) !important;
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
        $('#tabla-palomares').DataTable({
            "pageLength": 10,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "columnDefs": [
                { "orderable": false, "targets": -1 } // Desactivar orden en la columna de acciones
            ],
            "responsive": true,
            "autoWidth": false
        });

        // Auto-ocultar alertas después de 5 segundos
        $('.alert').delay(5000).fadeOut('slow');
    });
</script>
@endpush