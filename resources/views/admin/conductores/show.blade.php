@extends('layouts.app')

@section('subtitle', 'Detalle del Conductor')
@section('content_header_title', 'Conductores')
@section('content_header_subtitle', 'Detalle')

@section('content_body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <x-ops-card title="Perfil del Conductor" icon="user-tie" class="card-primary card-outline">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-tie fa-4x text-secondary"></i>
                    </div>
                    <h3 class="profile-username text-center">
                        {{ $conductor->nombre_completo }}
                    </h3>
                    <p class="text-muted text-center">
                        {{ $conductor->grado }}
                    </p>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Documento (Cédula)</b>
                            <span class="float-right text-dark font-weight-bold">
                                {{ $conductor->documento }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Estado</b>
                            <span class="float-right badge {{ $conductor->activo ? 'badge-success' : 'badge-secondary' }}">
                                {{ $conductor->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Licencia Conducir</b>
                            <span
                                class="float-right badge {{ $conductor->licencia_vigente ? 'badge-success' : 'badge-danger' }}">
                                {{ $conductor->categoria_licencia }} -
                                {{ $conductor->nro_licencia }}
                            </span>
                        </li>
                    </ul>
                    <div class="d-flex justify-content-between">

                        <x-btn-ops variant="secondary" icon="arrow-left" href="{{ route('admin.conductores.index') }}"
                            class="btn-sm">
                            Volver
                        </x-btn-ops>

                        @can('update', $conductor)
                            <x-btn-ops variant="warning" icon="edit" href="{{ route('admin.conductores.edit', $conductor) }}"
                                class="btn-sm">
                                Editar
                            </x-btn-ops>
                        @endcan
                    </div>
                </x-ops-card>
                <x-ops-card title="Observaciones" icon="comment-alt" class="card-primary">
                    <p class="text-muted mb-0">
                        {{ $conductor->observaciones ?? 'Sin observaciones registradas.' }}
                    </p>
                </x-ops-card>
            </div>
            <div class="col-md-8">

                <x-ops-card class="card-outline-ops">

                    {{-- PESTAÑAS --}}
                    <x-nav-tabs-ops :tabs="[
                        'documentacion' => 'Documentación Vencimientos',
                        'historial' => 'Últimas Salidas / Novedades',
                    ]" active="documentacion" />
                    <div class="tab-content">
                        <div class="active tab-pane" id="documentacion">
                            <div class="row">
                                {{-- LICENCIA --}}
                                <div class="col-sm-4">
                                    <x-ops-card title="Licencia de Conducir" icon="id-card" class="h-100">
                                        <div class="position-relative">
                                            <div class="ribbon-wrapper">
                                                <div
                                                    class="ribbon {{ $conductor->licencia_vigente ? 'bg-success' : 'bg-danger' }} text-xs">
                                                    {{ $conductor->licencia_vigente ? 'Vigente' : 'Vencido' }}
                                                </div>
                                            </div>
                                            <small class="text-muted d-block">
                                                Categoría:
                                                {{ $conductor->categoria_licencia }}
                                            </small>
                                            <small class="text-muted d-block">
                                                Nº:
                                                {{ $conductor->nro_licencia }}
                                            </small>
                                            <span class="text-sm font-weight-bold d-block mt-2">
                                                Vence:
                                                {{ $conductor->fecha_vencimiento_licencia->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    </x-ops-card>
                                </div>
                                <div class="col-sm-4">
                                    @if ($conductor->fecha_vencimiento_carne_salud)
                                        <x-ops-card title="Carné de Salud" icon="heartbeat" class="h-100">
                                            <div class="position-relative">
                                                <div class="ribbon-wrapper">
                                                    <div
                                                        class="ribbon {{ $conductor->carne_salud_vigente ? 'bg-success' : 'bg-danger' }} text-xs">
                                                        {{ $conductor->carne_salud_vigente ? 'Vigente' : 'Vencido' }}
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block">
                                                    Lugar:
                                                    {{ $conductor->lugar_carne_salud ?? 'No especificado' }}
                                                </small>
                                                <span class="text-sm font-weight-bold d-block mt-3">
                                                    Vence:
                                                    {{ $conductor->fecha_vencimiento_carne_salud->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </x-ops-card>
                                    @else
                                        <x-ops-card title="Carné de Salud" icon="heartbeat" class="h-100">
                                            <div class="d-flex align-items-center justify-content-center text-muted py-4">
                                                <span>Sin Carné de Salud</span>
                                            </div>
                                        </x-ops-card>
                                    @endif
                                </div>
                                <div class="col-sm-4">
                                    @if ($conductor->fecha_vencimiento_carne_habilitante)
                                        <x-ops-card title="Carné Habilitante" icon="certificate" class="h-100">
                                            <div class="position-relative">
                                                <div class="ribbon-wrapper">
                                                    <div
                                                        class="ribbon {{ $conductor->carne_habilitante_vigente ? 'bg-success' : 'bg-danger' }} text-xs">
                                                        {{ $conductor->carne_habilitante_vigente ? 'Vigente' : 'Vencido' }}
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block">
                                                    Habilitado:
                                                    {{ $conductor->tipo_vehiculo_habilitado ?? 'General' }}
                                                </small>
                                                <span class="text-sm font-weight-bold d-block mt-2">
                                                    Vence:
                                                    {{ $conductor->fecha_vencimiento_carne_habilitante->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </x-ops-card>
                                    @else
                                        <x-ops-card title="Carné Habilitante" icon="certificate" class="h-100">
                                            <div class="d-flex align-items-center justify-content-center text-muted py-4">
                                                <span>Sin Carné Habilitante</span>
                                            </div>
                                        </x-ops-card>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="historial">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha/Hora</th>
                                            <th>Vehículo</th>
                                            <th>Novedad / Destino</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($conductor->salidasVehiculos()
                                                            ->with('vehiculo')
                                                            ->latest('hora_sale')
                                                            ->limit(10)
                                                            ->get()
                                                        as $salida)
                                            <tr>
                                                <td>
                                                    {{ $salida->hora_sale->format('d/m/Y H:i') }}
                                                </td>

                                                <td>
                                                    {{ $salida->vehiculo?->matricula ?? 'N/A' }}
                                                </td>

                                                <td>
                                                    {{ Str::limit($salida->comision, 50) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">
                                                    No se registran salidas recientes
                                                    para este conductor.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </x-ops-card>
            </div>
        </div>
    </div>
@stop
