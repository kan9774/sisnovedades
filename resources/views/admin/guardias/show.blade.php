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
                    <form id="form-cerrar-forzado" action="{{ route('admin.guardias.cerrar', $guardia) }}" method="POST"
                        class="d-inline ml-2">
                        @csrf
                        <input type="hidden" name="forzar" value="1">
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="confirmarCierreForzado()">
                            Cerrar con novedades sin resolver
                        </button>
                    </form>
                @endif
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @php
            $puedeOperarGuardia = $guardia->esMiembro(auth()->user()) || auth()->user()->isAdmin();
        @endphp

        {{-- Info de la guardia --}}
        <x-ops-card>
            <x-slot:title>
                <i class="fas fa-shield-alt"></i>
                Guardia del {{ $guardia->date->format('d/m/Y') }}
            </x-slot:title>

            <x-slot:titleSuffix>
                @if ($guardia->status === 'open')
                    <span class="badge-ops badge-ops-success">Abierta</span>
                @else
                    <span class="badge-ops badge-ops-secondary">Cerrada</span>
                @endif
            </x-slot:titleSuffix>

            <x-slot:actions>
                @can('update', $guardia)
                    <a href="{{ route('admin.guardias.edit', $guardia) }}"
                        class="btn-ops btn-ops-secondary btn-ops-icon" data-toggle="tooltip" title="Editar guardia">
                        <i class="fas fa-edit text-warning"></i>
                    </a>
                @endcan
                @can('cerrar', $guardia)
                    <form id="form-cerrar-guardia" action="{{ route('admin.guardias.cerrar', $guardia) }}"
                        method="POST" class="d-inline">
                        @csrf
                        <button type="button" class="btn-ops btn-ops-secondary btn-ops-icon" data-toggle="tooltip"
                            title="Cerrar guardia" onclick="confirmarCierre()">
                            <i class="fas fa-lock text-danger"></i>
                        </button>
                    </form>
                @endcan
                @can('reactivar', $guardia)
                    <form id="form-reactivar-guardia" action="{{ route('admin.guardias.reactivar', $guardia) }}"
                        method="POST" class="d-inline">
                        @csrf
                        <button type="button" class="btn-ops btn-ops-secondary btn-ops-icon" data-toggle="tooltip"
                            title="Reactivar guardia" onclick="confirmarReactivacion()">
                            <i class="fas fa-lock-open text-warning"></i>
                        </button>
                    </form>
                @endcan
                @can('delete', $guardia)
                    <form id="form-eliminar-guardia" action="{{ route('admin.guardias.destroy', $guardia) }}"
                        method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn-ops btn-ops-secondary btn-ops-icon" data-toggle="tooltip"
                            title="Mover a papelera" onclick="confirmarEliminacionGuardia()">
                            <i class="fas fa-trash text-danger"></i>
                        </button>
                    </form>
                @endcan
                <a href="{{ route('admin.guardias.index') }}" class="btn-ops btn-ops-secondary btn-ops-icon"
                    data-toggle="tooltip" title="Volver">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </x-slot:actions>

            <div class="row">
                <div class="col-md-3">
                    <strong>Capitán de Servicio:</strong><br>
                    <span class="d-inline-flex align-items-center px-3 py-1 rounded border"
                        style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25); color: #007bff; font-size: 0.875rem;">
                        {{ $guardia->capitan->grade }} {{ $guardia->capitan->name }} {{ $guardia->capitan->last_name }}
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>Oficial de Día:</strong><br>
                    <span class="d-inline-flex align-items-center px-3 py-1 rounded border"
                        style="background-color: rgba(0, 255, 157, 0.178); border-color: rgba(0, 255, 21, 0.212); color: #007bff; font-size: 0.875rem;">
                    {{ $guardia->oficial->grade }} {{ $guardia->oficial->name }} {{ $guardia->oficial->last_name }}
                    </span>
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
                        <i class="fa-regular fa-file-pdf"></i>
                    </a>
                    @if ($puedeOperarGuardia)
                        <livewire:enviar-guardia-email :guardia="$guardia" :puede-operar-guardia="$puedeOperarGuardia" :key="'enviar-guardia-email-' . $guardia->id" />
                    @endif
                </div>
            </div>
            @if ($guardia->notes)
                <div class="row mt-2">
                    <div class="col-12"><strong>Notas:</strong> {{ $guardia->notes }}</div>
                </div>
            @endif
        </x-ops-card>

        {{-- Tabs --}}
        <div class="card-outline-ops mt-4">
            <ul class="nav nav-tabs-ops" id="guardia-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link-ops active" data-toggle="pill" href="#tab-novedades" role="tab">
                        <i class="fa-solid fa-tower-cell"></i> Novedades
                        <livewire:contador-guardia :guardia="$guardia" tipo="novedades" :key="'contador-novedades-' . $guardia->id" />
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-ops" data-toggle="pill" href="#tab-salidas" role="tab">
                        <i class="fas fa-truck"></i> Salidas de Vehículos
                        <livewire:contador-guardia :guardia="$guardia" tipo="salidas" :key="'contador-salidas-' . $guardia->id" />
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-ops" data-toggle="pill" href="#tab-personal" role="tab">
                        <i class="fas fa-users"></i> Personal
                        <livewire:contador-guardia :guardia="$guardia" tipo="personal" :key="'contador-personal-' . $guardia->id" />
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-ops" data-toggle="pill" href="#tab-rancho" role="tab">
                        <i class="fas fa-utensils"></i> Rancho
                    </a>
                </li>
                @if ($puedeOperarGuardia)
                    <li class="nav-item">
                        <a class="nav-link-ops" data-toggle="pill" href="#tab-correos-fallidos" role="tab">
                            <i class="fas fa-envelope-circle-check"></i> Correos fallidos
                            <livewire:badge-correos-fallidos :guardia="$guardia" :key="'badge-correos-fallidos-' . $guardia->id" />
                        </a>
                    </li>
                @endif
            </ul>

            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-novedades" role="tabpanel">
                        <livewire:novedades-guardia :guardia="$guardia" :puede-operar-guardia="$puedeOperarGuardia" :key="'novedades-guardia-' . $guardia->id" />
                    </div>
                    <div class="tab-pane" id="tab-salidas" role="tabpanel">
                        <livewire:salidas-vehiculo :guardia="$guardia" :puede-operar-guardia="$puedeOperarGuardia" :key="'salidas-vehiculo-' . $guardia->id" />
                        <livewire:salidas-pendientes :guardia="$guardia" :key="'salidas-pendientes-' . $guardia->id" />
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

                    @if ($puedeOperarGuardia)
                        <div class="tab-pane" id="tab-correos-fallidos" role="tabpanel">
                            <livewire:correos-fallidos :guardia="$guardia" :key="'correos-fallidos-' . $guardia->id" />
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        $(document).ready(function() {
            $('.alert:not(.alert-warning)').delay(3000).fadeOut('slow');

            const hash = window.location.hash;
            if (hash) {
                $(`a[href="${hash}"]`).tab('show');
            }
            $('#guardia-tabs a').on('shown.bs.tab', function(e) {
                history.replaceState(null, null, e.target.hash);
            });
        });

        function confirmarCierre() {
            confirmarAccion({
                title: '¿Cerrar la guardia?',
                icon: 'question',
                confirmButtonText: 'Sí, cerrar',
                onConfirm: () => document.getElementById('form-cerrar-guardia').submit(),
            });
        }

        function confirmarCierreForzado() {
            confirmarAccion({
                title: '¿Cerrar guardia con novedades sin resolver?',
                text: 'Esta acción queda registrada en el historial de auditoría.',
                confirmButtonText: 'Sí, cerrar de todas formas',
                onConfirm: () => document.getElementById('form-cerrar-forzado').submit(),
            });
        }

        function confirmarReactivacion() {
            confirmarAccion({
                title: '¿Reactivar la guardia?',
                icon: 'question',
                confirmButtonText: 'Sí, reactivar',
                confirmButtonColor: '#ffc107',
                onConfirm: () => document.getElementById('form-reactivar-guardia').submit(),
            });
        }

        function confirmarEliminacionGuardia() {
            confirmarAccion({
                title: '¿Eliminar la guardia del {{ $guardia->date->format('d/m/Y') }}?',
                text: 'La guardia se moverá a la papelera.',
                confirmButtonText: 'Sí, eliminar',
                onConfirm: () => document.getElementById('form-eliminar-guardia').submit(),
            });
        }
    </script>
@endpush