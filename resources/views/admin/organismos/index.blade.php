@extends('layouts.app')

@section('subtitle', 'Unidades')
@section('content_header_title', 'Unidades')
@section('content_header_subtitle', 'Listado')

@section('content_body')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building"></i> Unidades
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.organismos.create') }}" 
                       class="btn btn-outline-primary btn-sm"
                       style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                       aria-label="Crear nueva unidad">
                        <i class="fas fa-plus-circle"></i> Nueva Unidad
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Novedades</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organismos as $organismo)
                            <tr>
                                <td>{{ $organismo->nombre ?? $organismo->name }}</td>
                                <td>{{ $organismo->novedades()->count() }}</td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        {{-- Botón Editar --}}
                                        <a href="{{ route('admin.organismos.edit', $organismo) }}"
                                           class="btn btn-outline-warning btn-xs mr-1"
                                           style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                           aria-label="Editar organismo">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- Botón Eliminar --}}
                                        <form action="{{ route('admin.organismos.destroy', $organismo) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('¿Eliminar este organismo?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                    aria-label="Eliminar organismo">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    No hay unidades registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($organismos->hasPages())
                <div class="card-footer">
                    {{ $organismos->links() }}
                </div>
            @endif
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            // Auto-ocultar alertas después de 4 segundos
            $('.alert').delay(4000).fadeOut('slow');
        });
    </script>
@endpush