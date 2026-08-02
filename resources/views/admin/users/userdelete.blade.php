@extends('layouts.app')

@section('subtitle', 'Papelera')
@section('content_header_title', 'Usuarios')
@section('content_header_subtitle', 'Papelera')

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
                <h3 class="card-title-ops mb-0">Usuarios eliminados</h3>
                <span class="card-header-ops__eyebrow">{{ $userDelete->count() }} registros en papelera</span>
            </div>
            <div class="card-tools">
                <a href="{{ route('admin.users.index') }}" class="btn-ops btn-ops-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        <th>Grado</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Eliminado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userDelete as $user)
                        <tr>
                            <td>{{ $user->grade }}</td>
                            <td>{{ $user->name }} {{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge-ops badge-ops-secondary">
                                    {{ $user->rol->name ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $user->deleted_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                {{-- Restaurar --}}
                                <form action="{{ route('admin.users.restore', $user->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Restaurar este usuario?')">
                                    @csrf
                                    <button class="btn-ops btn-ops-success btn-xs">
                                        <i class="fas fa-undo"></i> Restaurar
                                    </button>
                                </form>

                                {{-- Eliminar permanentemente --}}
                                <form action="{{ route('admin.users.force-delete', $user->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar permanentemente? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-ops btn-ops-danger btn-xs">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay usuarios en la papelera.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('.alert').delay(4000).fadeOut('slow');
    });
</script>
@endpush