<div>
    @if ($successMsg)
        <div wire:key="success-{{ md5($successMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('successMsg', '')
        }, 4000)"
            x-transition class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ $successMsg }}
            <button type="button" class="close" wire:click="$set('successMsg', '')">&times;</button>
        </div>
    @endif

    <x-ops-card title="Notificaciones" icon="bell" eyebrow="{{ $notificaciones->total() }} mensajes">
        <x-slot name="actions">
            <button wire:click="marcarTodasLeidas" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-check-double"></i> Marcar todas como leídas
            </button>
        </x-slot>

        {{-- TOGGLE FILTRO --}}
        <div class="mb-3">
            <ul class="nav nav-pills nav-sm">
                <li class="nav-item">
                    <button class="nav-link {{ $filtro === 'todas' ? 'active' : '' }}"
                        wire:click="cambiarFiltro('todas')">
                        Todas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $filtro === 'no_leidas' ? 'active' : '' }}"
                        wire:click="cambiarFiltro('no_leidas')">
                        No leídas
                    </button>
                </li>
            </ul>
        </div>

        {{-- LISTA --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    @forelse ($notificaciones as $notificacion)
                        <tr class="{{ $notificacion->read_at ? '' : 'font-weight-bold bg-light' }}">
                            <td style="width: 30px;">
                                @if (!$notificacion->read_at)
                                    <span class="badge-ops badge-ops-danger">&nbsp;</span>
                                @endif
                            </td>
                            <td>
                                {{ $notificacion->data['mensaje'] ?? 'Notificación' }}
                                <br>
                                <small class="text-muted">
                                    {{ $notificacion->data['oficina'] ?? '' }} · {{ $notificacion->created_at->diffForHumans() }}
                                </small>
                            </td>
                            <td class="text-right" style="width: 150px;">
                                <button wire:click="marcarLeida('{{ $notificacion->id }}')"
                                    class="btn btn-sm btn-ops-primary">
                                    Ver novedad
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted p-4">No hay notificaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($notificaciones->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $notificaciones->links() }}
            </div>
        @endif
    </x-ops-card>
</div>
