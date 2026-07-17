<div>
    {{-- Alertas --}}
    @if($message)
    <div class="alert alert-{{ $messageType }} alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-{{ $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-triangle' : 'info-circle') }} mr-1"></i>
        {{ $message }}
        <button type="button" class="close" onclick="this.parentElement.style.display='none'">
            <span>&times;</span>
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
                <div class="col-md-4">
                    <button wire:click="quickCreate" class="btn btn-success btn-block" {{ $isRunning ? 'disabled' : '' }}>
                        <i class="fas fa-bolt mr-1"></i> Crear Backup Ahora
                    </button>
                </div>
                <div class="col-md-4">
                    <button wire:click="runCleanup" class="btn btn-warning btn-block" {{ $isRunning ? 'disabled' : '' }}>
                        <i class="fas fa-broom mr-1"></i> Limpiar Backups Viejos
                    </button>
                </div>
                <div class="col-md-4">
                    <button wire:click="$refresh" class="btn btn-info btn-block">
                        <i class="fas fa-sync-alt mr-1"></i> Refrescar Lista
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjeta: Estado --}}
    @if($isRunning)
    <div class="alert alert-info mb-3">
        <div class="d-flex align-items-center">
            <i class="fas fa-cog fa-spin fa-2x mr-3"></i>
            <div>
                <strong>Backup en progreso...</strong>
                <p class="mb-0 small">Esto puede tomar unos minutos dependiendo del tamaño de la base de datos.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Tarjeta: Lista de Backups --}}
    <div class="card card-outline card-dark">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i> Backups Existentes
            </h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ count($backups) }}</span> backups
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            @if(count($backups) > 0)
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
                    @foreach($backups as $backup)
                    <tr>
                        <td>
                            <i class="far fa-clock mr-1 text-muted"></i>
                            {{ $backup['date'] }}
                        </td>
                        <td>
                            <code>{{ $backup['name'] }}</code>
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $backup['size'] }}</span>
                        </td>
                        <td>
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle mr-1"></i> Completo
                            </span>
                        </td>
                        <td class="text-center">
                            <button wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Estás seguro de eliminar este backup?')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
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

    {{-- Tarjeta: Info --}}
    <div class="card card-outline card-info mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle mr-1"></i> Información
            </h3>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Rotación automática:</strong> Se mantienen backups de los últimos <strong>7 días</strong>.</li>
                <li><strong>Contenido:</strong> Solo base de datos (MySQL).</li>
                <li><strong>Almacenamiento:</strong> Máximo 5 GB total.</li>
                <li><strong>Automático:</strong> Para programar backups diarios, configurar tarea programada en Windows.</li>
            </ul>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('refresh-backups', () => {
        $wire.loadBackups();
    });
</script>
@endscript
