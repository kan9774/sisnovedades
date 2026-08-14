<div>
    {{-- Alertas --}}
    @if ($message)
        <div class="alert alert-{{ $messageType }} alert-dismissible fade show mb-3" role="alert">
            <i
                class="fas fa-{{ $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle') }} mr-1"></i>
            {{ $message }}
            <button type="button" class="close" onclick="this.parentElement.style.display='none'">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Estado de restauración en curso --}}
    @if ($restoreStatus !== 'idle' && $restoreStatus !== 'completed' && $restoreStatus !== 'failed')
        <div class="alert alert-info mb-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-cog fa-spin fa-2x mr-3"></i>
                <div>
                    <strong>
                        @if ($restoreStatus === 'backing_up_safety')
                            Creando backup de seguridad...
                        @elseif ($restoreStatus === 'restoring')
                            Restaurando base de datos...
                        @else
                            Procesando restauración...
                        @endif
                    </strong>
                    <p class="mb-0 small">
                        @if ($restoreStatus === 'backing_up_safety')
                            Se está generando un backup de seguridad del estado actual. Esto puede tardar.
                        @elseif ($restoreStatus === 'restoring')
                            La base de datos está en modo mantenimiento. No cierres esta página.
                        @else
                            Por favor esperá...
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Restauración completada con éxito --}}
    @if ($restoreStatus === 'completed')
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle mr-1"></i>
            <strong>Restauración completada exitosamente.</strong> La base de datos fue restaurada desde el backup seleccionado.
        </div>
    @endif

    {{-- Restauración fallida --}}
    @if ($restoreStatus === 'failed')
        <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <strong>Restauración fallida.</strong>
            <p class="mb-1 mt-2">{{ $restoreError ?? 'Error desconocido.' }}</p>
            @if ($restoreSafetyPath)
                <p class="mb-0">
                    <strong>Backup de seguridad disponible:</strong>
                    <code>{{ $restoreSafetyPath }}</code>
                    <br>
                    <small>Para restaurar manualmente, usá este archivo con el comando artisan correspondiente.</small>
                </p>
            @endif
            @if ($restoreLogPath)
                <p class="mb-0">
                    <strong>Logs detallados:</strong> storage/logs/{{ $restoreLogPath }}
                </p>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" wire:click="$set('restoreStatus', 'idle')">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    @endif

    {{-- Tarjeta: Acciones rápidas --}}
    <div class="card card-primary card-outline mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-database mr-1"></i> Acciones Rápidas
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <button wire:click="quickCreate" class="btn btn-success btn-block" wire:loading.attr="disabled"
                        wire:target="quickCreate">
                        <span wire:loading.remove wire:target="quickCreate">
                            <i class="fas fa-bolt mr-1"></i> Crear Backup Ahora
                        </span>
                        <span wire:loading wire:target="quickCreate">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Generando backup...
                        </span>
                    </button>
                </div>
                <div class="col-md-3">
                    <button wire:click="runCleanup" class="btn btn-warning btn-block" wire:loading.attr="disabled"
                        wire:target="runCleanup">
                        <span wire:loading.remove wire:target="runCleanup">
                            <i class="fas fa-broom mr-1"></i> Limpiar Backups Viejos
                        </span>
                        <span wire:loading wire:target="runCleanup">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Limpiando...
                        </span>
                    </button>
                </div>
                <div class="col-md-3">
                    <button wire:click="$refresh" class="btn btn-info btn-block" wire:loading.attr="disabled"
                        wire:target="$refresh">
                        <i class="fas fa-sync-alt mr-1"></i> Refrescar Lista
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Tarjeta: Lista de Backups --}}
    <div class="card-outline-ops">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i> Backups Existentes
            </h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ count($backups) }}</span> backups
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            @if (count($backups) > 0)
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Tamaño</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($backups as $backup)
                            <tr>
                                <td>
                                    <i class="far fa-clock mr-1 text-muted"></i>
                                    {{ $backup['date'] }}
                                </td>
                                <td>
                                    <code>{{ $backup['name'] }}</code>
                                </td>
                                <td>
                                    <span class="badge-ops badge-ops-secondary">{{ $backup['size'] }}</span>
                                </td>
                                <td>
                                    <span class="badge-ops badge-ops-success">
                                        <i class="fas fa-check-circle mr-1"></i> Completo
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                        class="btn btn-sm btn-danger"
                                        wire:loading.attr="disabled"
                                        wire:target="deleteBackup('{{ $backup['filename'] }}')"
                                        onclick="return confirm('¿Estás seguro de eliminar este backup?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @can('restore-backup')
                                        <button type="button" class="btn btn-sm btn-warning"
                                            wire:click="openRestore('{{ $backup['filename'] }}')"
                                            title="Restaurar desde este backup">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-database fa-3x mb-3"></i>
                    <p class="mb-0">No hay backups registrados.</p>
                    <p class="small">Hacé clic en "Crear Backup Ahora" para generar el primero.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de confirmación de restauración (ops-panel) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalRestoreConfirm" x-data x-init="$watch('$wire.showRestoreModal', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
         :class="{ 'is-open': $wire.showRestoreModal }"
         wire:click.self="$wire.closeRestoreModal()">
        <div class="ops-panel">
            <div class="ops-panel__header">
                <div class="ops-panel__title-wrap">
                    <span class="ops-panel__eyebrow">Restauración de Base de Datos</span>
                    <h5 class="ops-panel__title">Confirmar Restauración</h5>
                </div>
                <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalRestoreConfirm')" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="ops-panel__body">
                <div class="ops-panel__content">
                    @if ($selectedBackup)
                        <div class="form-group mb-3">
                            <label>Archivo de backup</label>
                            <code class="d-block p-2 bg-light">{{ $selectedBackup }}</code>
                        </div>

                        <div class="form-group mb-3">
                            <label>Nombre del backup</label>
                            <strong>{{ $selectedBackup ? pathinfo($selectedBackup, PATHINFO_FILENAME) : '—' }}</strong>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label>Motor detectado en el backup</label>
                                <span class="badge badge-{{ $detectedBackupDriver === 'pgsql' ? 'danger' : 'primary' }}">
                                    {{ ucfirst($detectedBackupDriver) }}
                                </span>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label>Motor activo actualmente</label>
                                <span class="badge badge-{{ $currentDriver === 'pgsql' ? 'danger' : 'success' }}">
                                    {{ ucfirst($currentDriver) }}
                                </span>
                            </div>
                        </div>

                        @if ($detectedBackupDriver !== $currentDriver)
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Motor incompatible:</strong> Este backup fue creado con
                                <strong>{{ ucfirst($detectedBackupDriver) }}</strong> pero la base de datos activa es
                                <strong>{{ ucfirst($currentDriver) }}</strong>. No se puede restaurar.
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <label>Tamaño del backup</label>
                            <span>{{ $selectedBackup ? (function() use ($backups) {
                                $b = collect($backups)->firstWhere('filename', $selectedBackup);
                                return $b ? $b['size'] : '—';
                            })() : '—' }}</span>
                        </div>

                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Se creará automáticamente un backup de seguridad del estado actual antes de proceder.
                        </div>

                        <div class="form-group mb-3">
                            <label>
                                Confirmá escribiendo el nombre del archivo de backup:
                                <strong>{{ $selectedBackup ? pathinfo($selectedBackup, PATHINFO_FILENAME) : '' }}</strong>
                                <small class="text-muted"> (sin extensión .zip)</small>
                            </label>
                            <input type="text"
                                   wire:model.live="restoreConfirmationText"
                                   class="form-control @error('restoreConfirmationText') is-invalid @enderror"
                                   placeholder="Escribí el nombre exacto aquí"
                                   autocomplete="off">
                            @error('restoreConfirmationText')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            <div class="ops-panel__footer">
                <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalRestoreConfirm')">
                    Cancelar
                </button>
                <button type="button"
                        class="btn btn-danger"
                        wire:click="startRestore"
                        disabled
                        @if ($detectedBackupDriver !== $currentDriver || !$selectedBackup || $restoreConfirmationText !== ($selectedBackup ? pathinfo($selectedBackup, PATHINFO_FILENAME) : ''))
                            disabled
                        @endif
                        wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="startRestore">
                        <i class="fas fa-rotate-left"></i> Confirmar Restauración
                    </span>
                    <span wire:loading wire:target="startRestore">
                        <i class="fas fa-spinner fa-spin"></i> Iniciando...
                    </span>
                </button>
            </div>
        </div>
    </div>
    </template>

    {{-- Tarjeta: Info --}}
    <div class="card-outline-ops mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle mr-1"></i> Información
            </h3>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Rotación automática:</strong> Se mantienen backups de los últimos <strong>7 días</strong>.
                </li>
                <li><strong>Contenido:</strong> Solo base de datos ({{ ucfirst(config('database.default')) }}).</li>
                <li><strong>Almacenamiento:</strong> Máximo 5 GB total.</li>
                <li><strong>Automático:</strong> Para programar backups diarios, configurar tarea programada en Windows.
                </li>
                <li><strong>Restauración:</strong> Solo admins. Requiere confirmación por nombre de archivo. Se crea backup de seguridad automático antes de restaurar.</li>
            </ul>
        </div>
    </div>
</div>

<div wire:poll.5s="getRestoreStatus" @if ($restoreStatus !== 'idle' && $restoreStatus !== 'completed' && $restoreStatus !== 'failed') visible @endif>
</div>

@script
    <script>
        if (!window.cerrarOpsPanel) {
            window.cerrarOpsPanel = function (id) {
                const overlay = document.getElementById(id);
                if (overlay) {
                    overlay.classList.remove('is-open');
                }
                document.body.classList.remove('ops-panel-open');
            };
        }

        $wire.on('refresh-backups', () => {
            $wire.loadBackups();
        });

        $wire.on('restore-completed', () => {
            $wire.getRestoreStatus();
        });

        $wire.$watch('showRestoreModal', (value) => {
            if (value) {
                document.getElementById('modalRestoreConfirm')?.classList.add('is-open');
                document.body.classList.add('ops-panel-open');
            } else {
                cerrarOpsPanel('modalRestoreConfirm');
            }
        });
    </script>
@endscript
