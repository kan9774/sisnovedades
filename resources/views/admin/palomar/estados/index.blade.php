@extends('layouts.app')

@section('subtitle', 'Estados de Palomas')
@section('content_header_title', 'Estados')
@section('content_header_subtitle', 'Catálogo')

@section('content_body')
<div class="container-fluid">

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Mensajes de error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-outline-ops">
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                <h3 class="card-title-ops mb-0">
                    <i class="fas fa-tags"></i> Estados de palomas
                </h3>
                <span class="card-header-ops__eyebrow">{{ $estados->total() ?? $estados->count() }} registros</span>
            </div>
            <div class="card-tools">
                <a href="{{ route('admin.estados-paloma.create') }}" 
                   class="btn-ops btn-ops-primary btn-sm"
                   aria-label="Crear nuevo estado">
                    <i class="fas fa-plus-circle"></i> Nuevo Estado
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Color</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estados as $estado)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $estado->nombre }}</strong>
                                </td>
                                <td>
                                    @if($estado->color)
                                        <span class="badge" style="background-color: {{ $estado->color }}; color: #fff; padding: 5px 12px; border-radius: 50px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                                            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: {{ $estado->color }}; border: 1px solid rgba(255,255,255,0.3);"></span>
                                            {{ $estado->color }}
                                        </span>
                                    @else
                                        <span class="text-muted">Sin color</span>
                                    @endif
                                </td>
                                <td>
                                    @if($estado->activo)
                                        <span class="badge-ops badge-ops-success">
                                            <i class="fas fa-check-circle mr-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge-ops badge-ops-secondary">
                                            <i class="fas fa-times-circle mr-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center flex-wrap gap-1">
                                        <a href="{{ route('admin.estados-paloma.edit', $estado) }}"
                                           class="btn-ops btn-ops-warning btn-xs"
                                           aria-label="Editar estado"
                                           title="Editar estado">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.estados-paloma.destroy', $estado) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este estado?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-ops btn-ops-danger btn-xs"
                                                    aria-label="Eliminar estado"
                                                    title="Eliminar estado">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-tags fa-2x d-block mb-2" style="opacity: 0.3;"></i>
                                    No hay estados registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($estados, 'hasPages') && $estados->hasPages())
            <div class="card-footer">
                {{ $estados->links() }}
            </div>
        @endif
    </div>
</div>
@stop

@push('css')
<style>
    /* Mejoras visuales para la tabla de estados */
    .table-responsive .badge {
        font-size: 0.85rem;
        padding: 4px 10px;
    }
    .table-responsive .badge.badge-success,
    .table-responsive .badge.badge-secondary {
        padding: 5px 12px;
        border-radius: 50px;
        font-weight: 500;
    }
    .table td, .table th {
        vertical-align: middle !important;
    }
    /* Botones xs */
    .btn-xs {
        padding: 0.1rem 0.4rem;
        font-size: 0.7rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    /* Gap para acciones */
    .gap-1 > * + * {
        margin-left: 0.25rem;
    }
    /* Responsive */
    @media (max-width: 576px) {
        .card-header .btn {
            font-size: 0.7rem !important;
            padding: 4px 10px !important;
        }
        .card-header h3 {
            font-size: 1rem !important;
        }
        .table-responsive table {
            font-size: 0.85rem;
        }
        .table-responsive .badge {
            font-size: 0.7rem;
            padding: 3px 8px;
        }
        .btn-xs {
            font-size: 0.6rem;
            padding: 0.05rem 0.3rem;
        }
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // Auto-ocultar alertas después de 5 segundos
        $('.alert').delay(5000).fadeOut('slow');
    });
</script>
@endpush