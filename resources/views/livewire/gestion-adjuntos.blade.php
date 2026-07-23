<div>
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-paperclip"></i> Adjuntos</h3>
        </div>
        <div class="card-body">

            @if (session('adjunto-success'))
                <div class="alert alert-success alert-dismissible" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                    {{ session('adjunto-success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Subir archivo(s) --}}
            @if ($this->puedeGestionar)
                <div class="mb-4">
                    <div class="custom-file">
                        <input type="file" wire:model="archivos" multiple
                            class="custom-file-input @error('archivos') is-invalid @enderror @error('archivos.*') is-invalid @enderror"
                            id="archivo-{{ $novedad->id }}" accept=".pdf,.jpg,.jpeg,.png">
                        <label class="custom-file-label" for="archivo-{{ $novedad->id }}">
                            Agregar archivo(s) (PDF, JPG, PNG — máx. 10MB c/u)
                        </label>
                    </div>

                    <div wire:loading wire:target="archivos" class="text-muted small mt-1">
                        <i class="fas fa-spinner fa-spin"></i> Subiendo archivo(s)...
                    </div>

                    @error('archivos')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror
                    @error('archivos.*')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror

                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i> Los archivos se suman a los que ya tiene la novedad (no se reemplazan). Podés seleccionar varios a la vez o subirlos de a uno.
                    </small>
                </div>
            @endif

            {{-- Listado --}}
            @forelse ($this->adjuntos as $adjunto)
                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2"
                    wire:key="adjunto-{{ $adjunto->id }}">
                    <div>
                        @if ($adjunto->esPdf())
                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                        @else
                            <i class="fas fa-file-image text-info mr-2"></i>
                        @endif
                        <strong>{{ $adjunto->file_name }}</strong>
                        <small class="text-muted ml-2">{{ $adjunto->tamanoLegible() }}</small>
                        <small class="text-muted ml-2">— {{ $adjunto->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.adjuntos.download', [$guardia, $novedad, $adjunto]) }}"
                            class="btn btn-info btn-xs">
                            <i class="fas fa-download"></i>
                        </a>
                        @if ($this->puedeGestionar)
                            <button type="button" wire:click="eliminar({{ $adjunto->id }})"
                                wire:confirm="¿Eliminar adjunto?" class="btn btn-danger btn-xs">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No hay archivos adjuntos.</p>
            @endforelse
        </div>
    </div>
</div>