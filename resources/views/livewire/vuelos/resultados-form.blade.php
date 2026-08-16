<div class="container-fluid">
    <x-ops-card title="Cargar Resultados" icon="flag-checkered"
        titleSuffix="— Vuelo del {{ $vuelo->fecha->format('d/m/Y') }}">

        @if ($successMsg)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $successMsg }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errorMsg)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errorMsg }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any() && !session()->has('success'))
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="novedad-texto mb-4">
            <span class="novedad-texto__eyebrow"><i class="fas fa-info-circle"></i> Información del Vuelo</span>
            <div class="row text-dark">
                <div class="col-md-4"><strong>Tipo:</strong> {{ ucfirst($vuelo->tipo) }}</div>
                <div class="col-md-4"><strong>Punto de liberación:</strong> {{ $vuelo->punto_liberacion ?? '-' }}</div>
                <div class="col-md-4"><strong>Hora de liberación:</strong>
                    {{ optional($vuelo->hora_liberacion)->format('H:i') ?? '-' }}</div>
            </div>
        </div>

        <form wire:submit="guardar">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle table-ops-hover">
                    <thead class="thead-ops">
                        <tr>
                            <th>Anilla</th>
                            <th>Nombre</th>
                            <th>Anilla competición</th>
                            <th>Distancia (km)</th>
                            <th>Hora llegada *</th>
                            <th>Posición</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vuelo->palomas as $paloma)
                            <tr>
                                <td><strong>{{ $paloma->anilla }}</strong></td>
                                <td>{{ $paloma->nombre ?? '-' }}</td>
                                <td>{{ $paloma->pivot->anilla_competicion ?? '-' }}</td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                        wire:model="datosResultados.{{ $paloma->id }}.distancia_km"
                                        class="form-control form-control-sm @error('datosResultados.'.$paloma->id.'.distancia_km') is-invalid @enderror">
                                    @error('datosResultados.'.$paloma->id.'.distancia_km')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input type="time"
                                        wire:model="datosResultados.{{ $paloma->id }}.hora_llegada"
                                        class="form-control form-control-sm @error('datosResultados.'.$paloma->id.'.hora_llegada') is-invalid @enderror">
                                    @error('datosResultados.'.$paloma->id.'.hora_llegada')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" min="1"
                                        wire:model="datosResultados.{{ $paloma->id }}.posicion"
                                        class="form-control form-control-sm @error('datosResultados.'.$paloma->id.'.posicion') is-invalid @enderror">
                                    @error('datosResultados.'.$paloma->id.'.posicion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input type="text"
                                        wire:model="datosResultados.{{ $paloma->id }}.observaciones"
                                        class="form-control form-control-sm @error('datosResultados.'.$paloma->id.'.observaciones') is-invalid @enderror">
                                    @error('datosResultados.'.$paloma->id.'.observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <div class="mt-4">
                    <button type="submit" class="btn-ops btn-ops-success btn-sm" wire:loading.attr="disabled" wire:target="guardar">
                        @if ($loading)
                            <span class="spinner-border spinner-border-sm mr-1"></span>
                        @endif
                        <i class="fas fa-flag-checkered"></i> Finalizar Vuelo y Guardar Resultados
                    </button>
                    <a href="{{ route('admin.vuelos.index') }}" class="btn-ops btn-ops-info btn-sm">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </x-ops-card>
</div>
