@extends('layouts.app')

@section('subtitle', 'Oficinas')
@section('content_header_title', 'Oficinas')
@section('content_header_subtitle', 'Listado')

@section('content_body')
    <div class="container-fluid">
        <div class="card card-outline-ops">
            <div class="card-header-ops">
                <div class="card-header-ops__title-wrap">
                    <h3 class="card-title-ops mb-0"><i class="fas fa-building"></i> Oficinas</h3>
                    <span class="card-header-ops__eyebrow">{{ $oficinas->count() }} registros</span>
                </div>
                <div class="card-tools">
                    <a href="{{ route('admin.oficinas.create') }}" class="btn-ops btn-ops-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Oficina
                    </a>
                </div>
            </div>
            <div class="card-body">

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

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="thead-ops">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuarios</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($oficinas as $oficina)
                                <tr>
                                    <td>{{ $oficina->nombre }}</td>
                                    <td>{{ $oficina->users_count }}</td>
                                    <td>
                                        @if ($oficina->activo)
                                            <span class="badge-ops badge-ops-success">Activa</span>
                                        @else
                                            <span class="badge-ops badge-ops-secondary">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.oficinas.edit', $oficina) }}"
                                            class="btn-ops btn-ops-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.oficinas.destroy', $oficina) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('¿Eliminar esta oficina?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ops btn-ops-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No hay oficinas cargadas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $oficinas->links() }}
            </div>
        </div>
    </div>
@stop