@extends('layouts.app')

@section('subtitle', 'Nuevo Rol')
@section('content_header_title', 'Roles')
@section('content_header_subtitle', 'Nuevo')

@section('content_body')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Crear rol</h3>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Nombre <small class="text-muted">(sin espacios, ej: oficial_de_dia)</small></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Descripción <small class="text-muted">(opcional)</small></label>
                        <input type="text" name="description"
                            class="form-control @error('description') is-invalid @enderror"
                            value="{{ old('description') }}">
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Permisos</label>

                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="buscadorPermisos" class="form-control"
                                   placeholder="Buscar permiso por nombre, descripción o módulo...">
                            <div class="input-group-append">
                                <button type="button" id="limpiarBuscadorPermisos" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small id="contadorPermisos" class="text-muted d-block mb-2"></small>

                        @foreach ($permisosPorModulo as $modulo => $permisosModulo)
                            <div class="card card-outline card-secondary mb-3" data-modulo-card
                                 data-modulo-buscar="{{ strtolower(str_replace('_', ' ', $modulo)) }}">
                                <div class="card-header py-2">
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-layer-group mr-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $modulo)) }}
                                    </h3>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        @foreach ($permisosModulo as $permiso)
                                            <div class="col-md-4" data-permiso-item
                                                 data-buscar="{{ strtolower($modulo . ' ' . str_replace('_', ' ', $permiso->name) . ' ' . $permiso->description) }}">
                                                <div class="custom-control custom-checkbox mb-2">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="permiso_{{ $permiso->id }}" name="permisos[]"
                                                        value="{{ $permiso->id }}">
                                                    <label class="custom-control-label" for="permiso_{{ $permiso->id }}">
                                                        {{ ucfirst(str_replace('_', ' ', $permiso->name)) }}
                                                        <br>
                                                        <small class="text-muted">{{ $permiso->description }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div id="sinResultadosPermisos" class="text-muted text-center py-3" style="display: none;">
                            No hay permisos que coincidan con la búsqueda.
                        </div>

                        @error('permisos')
                            <span class="text-danger d-block mb-2">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buscador = document.getElementById('buscadorPermisos');
            const limpiarBtn = document.getElementById('limpiarBuscadorPermisos');
            const contador = document.getElementById('contadorPermisos');
            const sinResultados = document.getElementById('sinResultadosPermisos');
            if (!buscador) return;

            const totalPermisos = document.querySelectorAll('[data-permiso-item]').length;

            function filtrar() {
                const termino = buscador.value.trim().toLowerCase();
                let visibles = 0;

                document.querySelectorAll('[data-modulo-card]').forEach(function (card) {
                    let algunoVisibleEnCard = false;

                    card.querySelectorAll('[data-permiso-item]').forEach(function (item) {
                        const coincide = termino === '' || item.dataset.buscar.includes(termino);
                        item.style.display = coincide ? '' : 'none';
                        if (coincide) {
                            algunoVisibleEnCard = true;
                            visibles++;
                        }
                    });

                    card.style.display = algunoVisibleEnCard ? '' : 'none';
                });

                sinResultados.style.display = (termino !== '' && visibles === 0) ? '' : 'none';
                contador.textContent = termino === ''
                    ? ''
                    : `Mostrando ${visibles} de ${totalPermisos} permisos`;
            }

            buscador.addEventListener('input', filtrar);

            limpiarBtn.addEventListener('click', function () {
                buscador.value = '';
                filtrar();
                buscador.focus();
            });
        });
    </script>
@stop