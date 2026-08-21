@php
    $descripciones = [
        'usuarios_alta' => 'Selector de unidades al dar de alta un usuario.',
        'usuarios_edicion' => 'Selector de unidades al editar un usuario existente.',
        'usuarios_registro' => 'Selector de unidades en el registro de acceso (autoregistro).',
        'vehiculos_form' => 'Selector de unidades en el formulario de vehículos.',
        'vehiculos_tabs' => 'Pestañas por unidad en el listado de vehículos.',
        'guardias_rancho' => 'Selector de unidades para las novedades de rancho de la guardia.',
        'apoyos_asignacion' => 'Campo "A quién se dispuso" en Apoyos S-4.',
        'pase' => 'Selector de unidades del panel de pases de personal.',
        'comision' => 'Selector de unidades del panel de comisiones.',
    ];
@endphp

<div>
    <x-ops-card
        title="Unidades por Módulo"
        icon="table-cells"
        eyebrow="{{ $unidades->count() }} unidades · {{ count($modulos) }} módulos">

        <div class="alert alert-info mb-3">
            <i class="fas fa-circle-info"></i>
            Cada casilla define qué unidades aparecen en el selector de ese módulo.
            El cambio aplica <strong>de inmediato</strong> y solo afecta selecciones futuras:
            los registros ya guardados (apoyos, pases, vehículos, etc.) no se modifican.
            La columna <span class="badge bg-info text-dark">Usos</span> muestra cuántos datos guardados
            referencian cada unidad hoy.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th style="min-width: 220px">Unidad</th>
                        <th class="text-center" title="Registros guardados que referencian esta unidad (contexto informativo)">
                            Usos
                        </th>
                        @foreach ($modulos as $modulo)
                            <th class="text-center text-wrap" style="min-width: 110px"
                                title="{{ $descripciones[$modulo] ?? '' }}">
                                {{ $etiquetas[$modulo] ?? $modulo }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-50" wire:target="toggle">
                    @forelse ($unidades as $unidad)
                        @php $usos = $usosPorUnidad[$unidad->id] ?? null; @endphp
                        <tr wire:key="fila-unidad-{{ $unidad->id }}">
                            <td>
                                <strong>{{ $unidad->nombre }}</strong>
                                <span class="badge {{ $unidad->activo ? 'bg-success' : 'bg-secondary' }} ms-1">
                                    {{ $unidad->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($usos)
                                    <span class="badge bg-info text-dark"
                                        title="{{ $usos['detalle'] }} — quitarla de un módulo NO borra estos registros.">
                                        {{ $usos['total'] }}
                                    </span>
                                @else
                                    <span class="text-muted" title="Sin registros que referencien esta unidad">&mdash;</span>
                                @endif
                            </td>
                            @foreach ($modulos as $modulo)
                                <td class="text-center" wire:key="celda-{{ $unidad->id }}-{{ $modulo }}">
                                    <input type="checkbox" class="form-check-input"
                                        aria-label="{{ $unidad->nombre }} en {{ $etiquetas[$modulo] ?? $modulo }}"
                                        title="{{ $descripciones[$modulo] ?? '' }}"
                                        {{ ($pivotes["{$unidad->id}:{$modulo}"] ?? false) ? 'checked' : '' }}
                                        wire:click="toggle({{ $unidad->id }}, '{{ $modulo }}')"
                                        wire:loading.attr="disabled" wire:target="toggle">
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($modulos) + 2 }}" class="text-center text-muted py-4">
                                No hay unidades cargadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 small text-muted">
            <i class="fas fa-shield-halved"></i>
            Las unidades marcadas como <span class="badge bg-secondary">Inactiva</span> nunca aparecen en los selectores,
            aunque estén tildadas aquí; la casilla se conserva por si se reactiva la unidad.
        </div>

        {{-- LEYENDA DE MÓDULOS --}}
        <div class="row g-2 mt-3">
            @foreach ($descripciones as $clave => $descripcion)
                <div class="col-md-4" wire:key="leyenda-{{ $clave }}">
                    <div class="border rounded p-2 h-100">
                        <strong><code>{{ $clave }}</code></strong> &mdash; {{ $etiquetas[$clave] ?? $clave }}
                        <div class="text-muted">{{ $descripcion }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ops-card>

    @script
    <script>
        $wire.$watch('successMsg', (valor) => {
            mostrarToast('success', valor);
        });

        $wire.$watch('errorMsg', (valor) => {
            mostrarToast('error', valor);
        });
    </script>
    @endscript
</div>
