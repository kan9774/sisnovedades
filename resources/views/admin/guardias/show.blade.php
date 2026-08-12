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
                    <button type="button" class="btn btn-sm btn-outline-dark"
                        wire:click="$dispatchTo('guardia-acciones-{{ $guardia->id }}', 'cerrarForzadoDesdeAlerta')">
                        Cerrar con novedades sin resolver
                    </button>
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
                @livewire('guardia-acciones', ['guardia' => $guardia], key('guardia-acciones-' . $guardia->id))
                @can('delete', $guardia)
                    <form id="form-eliminar-guardia" action="{{ route('admin.guardias.destroy', $guardia) }}" method="POST"
                        class="d-inline">
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
                    <a href="{{ route('admin.guardias.pdf', $guardia) }}" id="btnPdfDirecto"
                        class="btn btn-outline-danger btn-ml ml-1 align-items-center" data-toggle="tooltip"
                        title="Imprimir Guardia (sin firma)" target="_blank">
                        <i class="fa-regular fa-file-pdf"></i>
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-ml ml-1" data-toggle="modal"
                        data-target="#modalFirmaPdf" data-toggle2="tooltip" title="Configurar firma del PDF">
                        <i class="fas fa-signature"></i>
                    </button>
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
        {{-- Modal: selección de firma para el PDF --}}
        <div class="modal fade" id="modalFirmaPdf" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-signature"></i> Firma a incluir en el PDF</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Seleccioná qué firma va al pie del documento. Si no marcás ninguna, el PDF se genera sin firma.
                        </p>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="firmaCapitan" value="capitan">
                            <label class="custom-control-label" for="firmaCapitan">Capitán de Servicio</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="firmaOficial" value="oficial">
                            <label class="custom-control-label" for="firmaOficial">Oficial de Día</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-outline-danger" id="btnGenerarPdfFirma">
                            <i class="fa-regular fa-file-pdf"></i> Generar PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>


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

        function confirmarEliminacionGuardia() {
            confirmarAccion({
                title: '¿Eliminar la guardia del {{ $guardia->date->format('d/m/Y') }}?',
                text: 'La guardia se moverá a la papelera.',
                confirmButtonText: 'Sí, eliminar',
                onConfirm: () => document.getElementById('form-eliminar-guardia').submit(),
            });
        }
        document.getElementById('btnGenerarPdfFirma').addEventListener('click', function() {
            const firmas = [];
            if (document.getElementById('firmaCapitan').checked) firmas.push('capitan');
            if (document.getElementById('firmaOficial').checked) firmas.push('oficial');

            const qs = firmas.map(f => `firma[]=${f}`).join('&');
            const url = "{{ route('admin.guardias.pdf', $guardia) }}" + (qs ? `?${qs}` : '');

            window.open(url, '_blank');
            $('#modalFirmaPdf').modal('hide');
        });
    </script>
@endpush
