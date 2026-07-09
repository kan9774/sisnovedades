@extends('adminlte::page')

@section('title', 'Notificaciones')

@section('content_header')
    <h1>Notificaciones</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <ul class="nav nav-pills mb-0">
            <li class="nav-item">
                <a class="nav-link {{ $filtro === 'todas' ? 'active' : '' }}"
                   href="{{ route('admin.notificaciones.index', ['filtro' => 'todas']) }}">
                    Todas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $filtro === 'no_leidas' ? 'active' : '' }}"
                   href="{{ route('admin.notificaciones.index', ['filtro' => 'no_leidas']) }}">
                    No leídas
                </a>
            </li>
        </ul>

        <form action="{{ route('admin.notificaciones.marcar-todas') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                Marcar todas como leídas
            </button>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <tbody>
                @forelse($notificaciones as $notificacion)
                    <tr class="{{ $notificacion->read_at ? '' : 'font-weight-bold bg-light' }}">
                        <td style="width: 30px;">
                            @if(!$notificacion->read_at)
                                <span class="badge badge-danger">&nbsp;</span>
                            @endif
                        </td>
                        <td>
                            {{ $notificacion->data['mensaje'] ?? 'Notificación' }}
                            <br>
                            <small class="text-muted">
                                {{ $notificacion->data['oficina'] ?? '' }} · {{ $notificacion->created_at->diffForHumans() }}
                            </small>
                        </td>
                        <td class="text-right" style="width: 200px;">
                            <form action="{{ route('admin.notificaciones.leer', $notificacion->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">
                                    Ver novedad
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted p-4">No hay notificaciones.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $notificaciones->appends(['filtro' => $filtro])->links() }}
    </div>
</div>
@endsection