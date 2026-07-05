@extends('layouts.app')

@section('subtitle', 'Guardias')
@section('content_header_title', 'Guardias')
@section('content_header_subtitle', 'Listado')

@section('content_body')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header ">
                <h3 class="card-title mb-0">
                    <i class="fas fa-shield-alt me-2"></i> Guardias registradas
                </h3>
                <div class="card-tools">
                    @can('viewTrashed', App\Models\Guard::class)
                        <a href="{{ route('admin.guardias.trashed') }}" class="btn btn-outline-secondary btn-sm me-1"
                            data-toggle="tooltip" title="Ver guardias eliminadas">
                            <i class="fas fa-trash-alt"></i> Papelera
                        </a>
                    @endcan
                    @can('create', App\Models\Guard::class)
                        <a href="{{ route('admin.guardias.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus-circle"></i> Nueva guardia
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th class="py-3">Fecha</th>
                                <th class="py-3">Capitán</th>
                                <th class="py-3">Oficial de Día</th>
                                <th class="py-3">Estado</th>
                                <th class="py-3 text-center">Novedades</th>
                                <th class="py-3 text-center" style="min-width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guardias as $guardia)
                                <tr class="border-bottom">
                                    <td class="fw-bold">
                                        <i class="far fa-calendar-alt text-primary me-1"></i>
                                        {{ $guardia->date->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        {{ $guardia->capitan->name }} {{ $guardia->capitan->last_name }}
                                    </td>
                                    <td>
                                        {{ $guardia->oficial->name }} {{ $guardia->oficial->last_name }}
                                    </td>
                                    <td>
                                        @if ($guardia->status === 'open')
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                Abierta
                                            </span>
                                        @else
                                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                Cerrada
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $guardia->novedades_count ?? $guardia->novedades->count() }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-end">
                                            {{-- Botón Ver --}}
                                            <a href="{{ route('admin.guardias.show', $guardia) }}"
                                                class="btn btn-outline-info btn-sm mr-2" data-toggle="tooltip"
                                                title="Ver detalles de la guardia">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- Botón Eliminar (solo Super Admin) --}}
                                            @can('delete', $guardia)
                                                <form action="{{ route('admin.guardias.destroy', $guardia) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('¿Eliminar la guardia del {{ $guardia->date->format('d/m/Y') }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" data-toggle="tooltip"
                                                        title="Mover a papelera">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x d-block mb-3 text-secondary"></i>
                                        <span class="fs-5">No hay guardias registradas.</span>
                                        @can('create', App\Models\Guard::class)
                                            <br>
                                            <a href="{{ route('admin.guardias.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="fas fa-plus-circle"></i> Crear la primera
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($guardias->hasPages())
                <div class="card-footer bg-light border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Mostrando {{ $guardias->firstItem() ?? 0 }} - {{ $guardias->lastItem() ?? 0 }}
                            de {{ $guardias->total() }} guardias
                        </small>
                        {{ $guardias->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Auto-ocultar alertas después de 5 segundos
            $('.alert').delay(5000).slideUp(300, function() {
                $(this).alert('close');
            });
        });
    </script>
@endpush
