@extends('layouts.app')

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

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible">
                {{ session('warning') }}
                @if ($guardia->status === 'open')
                    <form action="{{ route('admin.guardias.cerrar', $guardia) }}" method="POST" class="d-inline ml-2">
                        @csrf
                        <input type="hidden" name="forzar" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-dark"
                            onclick="return confirm('¿Confirmás cerrar la guardia con novedades sin resolver?')">
                            Cerrar de todas formas
                        </button>
                    </form>
                @endif
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        {{-- Info de la guardia (sin cambios respecto a lo que ya tenías) --}}
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
                        @can('cerrar', $guardia)
                            <form action="{{ route('admin.guardias.cerrar', $guardia) }}" method="POST" class="d-inline ml-1">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm" data-toggle="tooltip" title="Cerrar guardia"
                                    onclick="return confirm('¿Cerrar la guardia?')">
                                    <i class="fas fa-lock"></i>
                                </button>
                            </form>
                        @endcan
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
                        {{ $guardia->oficial->grade }} {{ $guardia->oficial->name }} {{ $guardia->oficial->last_name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Escribientes:</strong><br>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @forelse($guardia->escribiente as $escribiente)
                                <span class="d-inline-flex align-items-center px-3 py-1 rounded border"
                                    style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25); color: #007bff; font-size: 0.875rem;">
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
                        <div class="col-12"><strong>Notas:</strong> {{ $guardia->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        @php
            $puedeOperarGuardia =
                $guardia->captain_id === auth()->id() ||
                $guardia->oficer_id === auth()->id() ||
                $guardia->escribiente->contains('id', auth()->id()) ||
                auth()->user()->isAdmin();
        @endphp

        {{-- Tabs --}}
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="guardia-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="pill" href="#tab-novedades" role="tab">
                            <i class="fa-solid fa-tower-cell"></i> Novedades
                            <span class="badge badge-primary ml-1">{{ $novedadesCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#tab-salidas" role="tab">
                            <i class="fas fa-truck"></i> Salidas de Vehículos
                            <span class="badge badge-primary ml-1">{{ $salidasCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#tab-personal" role="tab">
                            <i class="fas fa-users"></i> Personal
                            <span class="badge badge-primary ml-1">{{ $novedadesPersonalCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#tab-rancho" role="tab">
                            <i class="fas fa-utensils"></i> Rancho
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-novedades" role="tabpanel">
                        <livewire:novedades-guardia :guardia="$guardia" :puede-operar-guardia="$puedeOperarGuardia" :key="'novedades-guardia-' . $guardia->id" />
                    </div>
                    <div class="tab-pane" id="tab-salidas" role="tabpanel">
                        <livewire:salidas-vehiculo :guardia="$guardia" :puede-operar-guardia="$puedeOperarGuardia" :key="'salidas-vehiculo-' . $guardia->id" />
                    </div>
                    <div class="tab-pane" id="tab-personal" role="tabpanel">
                        <livewire:novedades-personal :guardia="$guardia" :puede-operar-guardia="$puedeOperarGuardia" :key="'novedades-personal-' . $guardia->id" />
                    </div>
                    <div class="tab-pane" id="tab-rancho" role="tabpanel">
                        @include('admin.guardias.partials._rancho', [
                            'guardia' => $guardia,
                            'unidadesActivas' => $unidadesActivas,
                            'rancho' => $rancho,
                            'puedeOperarGuardia' => $puedeOperarGuardia,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        $(document).ready(function() {
            $('.alert').delay(3000).fadeOut('slow');

            const hash = window.location.hash;
            if (hash) {
                $(`a[href="${hash}"]`).tab('show');
            }
            $('#guardia-tabs a').on('shown.bs.tab', function(e) {
                history.replaceState(null, null, e.target.hash);
            });
        });
    </script>
@endpush
