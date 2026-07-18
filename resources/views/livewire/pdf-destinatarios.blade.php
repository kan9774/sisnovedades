<div>
    {{-- Header --}}
    <div class="mb-4">
        <h4 class="mb-1">
            <i class="fas fa-address-book text-primary"></i>
            Grupos de Destinatarios
        </h4>
        <p class="text-muted mb-0">Gestioná los grupos para enviar la guardia por PDF</p>
    </div>

    {{-- Tabla --}}
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-list"></i>
                {{ $showTrashed ? 'Papelera' : 'Grupos activos' }}
                <span class="badge bg-secondary ml-2">{{ $destinatarios->total() }}</span>
            </h3>
            <div class="card-tools d-flex align-items-center mb-0">
                <div class="form-check form-switch mr-3 mb-0">
                    <input class="form-check-input" type="checkbox" id="toggleTrashed" wire:click="toggleTrashed" wire:model.live="showTrashed">
                    <label class="form-check-label" for="toggleTrashed">
                        <i class="fas fa-trash-alt"></i> Ver eliminados
                    </label>
                </div>
                <button type="button" class="btn btn-primary btn-sm" wire:click="crear">
                    <i class="fas fa-plus-circle"></i> Nuevo Grupo
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if ($destinatarios->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Grupo</th>
                                <th>Descripción</th>
                                <th>Detalles</th>
                                <th style="width: 120px;">Usuarios</th>
                                <th style="width: 100px;">Color</th>
                                @if ($showTrashed)
                                    <th style="width: 100px;">Eliminado</th>
                                @endif
                                <th style="width: 220px;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($destinatarios as $index => $dest)
                                <tr wire:key="dest-{{ $dest->id }}">
                                    <td class="text-muted">{{ $index + 1 + ($destinatarios->currentPage() - 1) * $destinatarios->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge d-inline-block mr-2" style="background-color: {{ $dest->color }};">
                                                <i class="fas fa-users"></i>
                                            </span>
                                            <strong>{{ $dest->nombre }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $dest->descripcion ?? '-' }}</td>
                                    <td class="text-muted" style="max-width: 300px;">
                                        {{ $dest->detalles ? Str::limit($dest->detalles, 50) : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $dest->usuarios->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge d-inline-block mr-1" style="background-color: {{ $dest->color }}; width: 20px; height: 20px;"></span>
                                            <small>{{ $dest->color }}</small>
                                        </div>
                                    </td>
                                    @if ($showTrashed)
                                        <td>
                                            <small class="text-muted">{{ $dest->deleted_at->diffForHumans() }}</small>
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center flex-wrap">
                                            @if ($showTrashed)
                                                <button type="button" class="btn btn-sm btn-outline-success mr-1 mt-1"
                                                    wire:click="restaurar({{ $dest->id }})"
                                                    title="Restaurar" wire:confirm="¿Restaurar este grupo?">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1"
                                                    wire:click="eliminar({{ $dest->id }})"
                                                    title="Eliminar permanentemente" wire:confirm="¿Eliminar permanentemente?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-primary mr-1 mt-1"
                                                    wire:click="editar({{ $dest->id }})"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info mr-1 mt-1"
                                                    wire:click="asignar({{ $dest->id }})"
                                                    title="Asignar usuarios">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1"
                                                    wire:click="eliminar({{ $dest->id }})"
                                                    title="Mover a papelera" wire:confirm="¿Mover a papelera?">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="p-3 bg-light border-top">
                    {{ $destinatarios->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">
                        @if ($showTrashed)
                            No hay grupos en la papelera.
                        @else
                            No hay grupos creados. Hacé clic en "Nuevo Grupo" para empezar.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Crear/Editar --}}
    @if ($creating || $editing)
    <div class="modal fade show d-block" tabindex="-1" wire:ignore.self
        style="background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit="guardar">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-{{ $editing ? 'edit' : 'plus-circle' }}"></i>
                            {{ $editing ? 'Editar Grupo' : 'Nuevo Grupo' }}
                        </h5>
                        <button type="button" class="close" wire:click="resetForm">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                placeholder="Ej: Capitanes, Oficiales de Día...">
                            @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <input type="text" wire:model="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                                placeholder="Breve descripción del grupo">
                            @error('descripcion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Detalles</label>
                            <textarea wire:model="detalles" rows="3" class="form-control @error('detalles') is-invalid @enderror"
                                placeholder="Detalles adicionales del grupo"></textarea>
                            @error('detalles') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" wire:model="color" class="form-control form-control-color"
                                    id="colorPicker" value="{{ $color }}">
                                <input type="text" wire:model="color" class="form-control @error('color') is-invalid @enderror"
                                    value="{{ $color }}" style="width: 100px;">
                                @error('color') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="mt-2">
                                <span class="badge" style="background-color: {{ $color }}; padding: 10px 20px;">
                                    <i class="fas fa-users"></i> {{ $color }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="resetForm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="guardar">
                                <i class="fas fa-save"></i> {{ $editing ? 'Actualizar' : 'Crear' }}
                            </span>
                            <span wire:loading wire:target="guardar">
                                <i class="fas fa-spinner fa-spin"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Asignar Usuarios --}}
    @if ($assigning)
    <div class="modal fade show d-block" tabindex="-1" wire:ignore.self
        style="background: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit="guardarAsignacion">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus"></i>
                            Asignar Usuarios - {{ $destinatarios->firstWhere('id', $destinatarioId)?->nombre }}
                        </h5>
                        <button type="button" class="close" wire:click="cerrarAsignacion">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" wire:click="toggleAllUsers" id="selectAll">
                                <strong>Seleccionar todos</strong>
                            </label>
                        </div>

                        <hr>

                        @foreach ($users->groupBy(fn ($user) => $user->oficina->nombre ?? 'Sin oficina') as $oficinaNombre => $usuarios)
                            <div class="mb-3">
                                <h6 class="text-primary">
                                    <i class="fas fa-building"></i> {{ $oficinaNombre }}
                                </h6>
                                @foreach ($usuarios as $usuario)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            wire:model="selectedUsers" value="{{ $usuario->id }}"
                                            id="user-{{ $usuario->id }}">
                                        <label class="form-check-label" for="user-{{ $usuario->id }}">
                                            {{ $usuario->grade }} {{ $usuario->name }} {{ $usuario->last_name }}
                                            <br><small class="text-muted">{{ $usuario->email }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cerrarAsignacion">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="guardarAsignacion">
                                <i class="fas fa-save"></i> Guardar Asignación
                            </span>
                            <span wire:loading wire:target="guardarAsignacion">
                                <i class="fas fa-spinner fa-spin"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Scripts para eventos --}}
    @script
    <script>
        Livewire.on('destinatario-guardado', () => {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'El grupo se guardó correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
        });

        Livewire.on('destinatario-eliminado', () => {
            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: 'El grupo se movió a la papelera.',
                timer: 2000,
                showConfirmButton: false
            });
        });

        Livewire.on('destinatario-restaurado', () => {
            Swal.fire({
                icon: 'success',
                title: '¡Restaurado!',
                text: 'El grupo se restauró correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
        });

        Livewire.on('destinatario-actualizado', () => {
            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: 'Los usuarios se asignaron correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
        });
    </script>
    @endscript
</div>