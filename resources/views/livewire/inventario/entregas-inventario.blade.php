<div
x-data
x-on:comprobante-listo.window="window.open($event.detail.url,'_blank')"
>
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        {{-- Encabezado: tipo, origen, destino, líneas --}}
        <div class="col-md-7">
            <x-ops-card>
                <x-slot:header>
                    <x-nav-tabs-ops
                        :tabs="['entrega' => 'Entrega', 'devolucion' => 'Devolución']"
                        :active="$tipo"
                        wireMethod="cambiarTipo"
                        :livewire="true"
                    />
                </x-slot:header>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>{{ $tipo === 'entrega' ? 'Origen (depósito)' : 'Origen (quién devuelve)' }}</label>
                        <select wire:model.live="origenId" class="form-control @error('origenId') is-invalid @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach ($origenes as $ubicacion)
                                <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                            @endforeach
                        </select>
                        @error('origenId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ $tipo === 'entrega' ? 'Destino (quién recibe)' : 'Destino (depósito)' }}</label>
                        <select wire:model="destinoId" class="form-control @error('destinoId') is-invalid @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach ($destinos as $ubicacion)
                                <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                            @endforeach
                        </select>
                        @error('destinoId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if ($tipo === 'devolucion' && $origenId)
                    <button type="button" wire:click="cargarTodoElOrigen" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="fas fa-boxes"></i> Cargar todo lo asignado en esta ubicación
                    </button>
                @endif

                <hr>

                {{-- Agregar línea --}}
                <div class="row align-items-end">
                    <div class="form-group col-md-5">
                        <label>Ítem</label>
                        <select wire:model.live="lineaItemId" class="form-control @error('lineaItemId') is-invalid @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->codigo }} — {{ $item->nombre }}</option>
                            @endforeach
                        </select>
                        @error('lineaItemId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    @php($itemSeleccionado = $lineaItemId ? $items->firstWhere('id', $lineaItemId) : null)

                    @if ($itemSeleccionado?->esPorCantidad())
                        <div class="form-group col-md-4">
                            <label>Cantidad</label>
                            <input type="number" wire:model="lineaCantidad" min="1"
                                   class="form-control @error('lineaCantidad') is-invalid @enderror">
                            @error('lineaCantidad') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    @elseif ($itemSeleccionado?->esIndividual())
                        <div class="form-group col-md-4">
                            <label>Unidad puntual</label>
                            <select wire:model="lineaItemUnidadId" class="form-control @error('lineaItemUnidadId') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach ($unidadesCandidatas as $unidad)
                                    <option value="{{ $unidad->id }}">{{ $unidad->numero_serie ?? "Sin nº de serie (#{$unidad->id})" }}</option>
                                @endforeach
                            </select>
                            @error('lineaItemUnidadId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            @if ($origenId && $unidadesCandidatas->isEmpty())
                                <small class="form-text text-danger">No hay unidades de este ítem disponibles en el origen.</small>
                            @endif
                        </div>
                    @endif

                    <div class="form-group col-md-3">
                        <x-btn-ops type="button" variant="primary" class="w-100 justify-content-center"
                            wire:click="agregarLinea" @disabled(! $origenId || ! $lineaItemId)>
                            <i class="fas fa-plus"></i> Agregar
                        </x-btn-ops>
                    </div>
                </div>

                <div class="form-group">
                    <label>Motivo / observaciones (opcional)</label>
                    <textarea wire:model="motivo" class="form-control" rows="2"></textarea>
                </div>
            </x-ops-card>
        </div>

        {{-- Carrito --}}
        <div class="col-md-5">
            <x-ops-card eyebrow="BCOM1 · Inventario" icon="clipboard-list"
                :title="$tipo === 'entrega' ? 'A entregar' : 'A devolver'"
                :titleSuffix="count($lineas)">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse ($lineas as $indice => $linea)
                                <tr wire:key="linea-{{ $indice }}">
                                    <td>
                                        {{ $linea['item_nombre'] }}
                                        @if ($linea['tipo_seguimiento'] === 'individual')
                                            <br><small class="text-muted">Nº {{ $linea['numero_serie'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($linea['tipo_seguimiento'] === 'cantidad')
                                            <span class="badge-ops badge-ops-secondary">{{ $linea['cantidad'] }}</span>
                                        @else
                                            <span class="badge-ops badge-ops-info">1 unidad</span>
                                        @endif
                                    </td>
                                    <td class="text-right" style="width: 40px">
                                        <button type="button" wire:click="quitarLinea({{ $indice }})"
                                                class="btn-ops btn-ops-danger btn-ops-icon btn-ops-icon--sm" title="Quitar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center text-muted py-4">
                                        Todavía no agregaste ítems.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-slot:footer>
                    <x-btn-ops type="button" variant="primary" class="w-100 justify-content-center"
                        wire:click="confirmar" wire:loading.attr="disabled" wire:target="confirmar"
                        @disabled(empty($lineas))>
                        <span wire:loading.remove wire:target="confirmar">
                            <i class="fas fa-check"></i> Confirmar {{ $tipo === 'entrega' ? 'entrega' : 'devolución' }}
                        </span>
                        <span wire:loading wire:target="confirmar">
                            <i class="fas fa-spinner fa-spin"></i> Procesando...
                        </span>
                    </x-btn-ops>
                </x-slot:footer>
            </x-ops-card>
        </div>
    </div>
</div>