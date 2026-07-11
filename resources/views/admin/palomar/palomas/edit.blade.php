@extends('layouts.app')

@section('subtitle', 'Editar Paloma')
@section('content_header_title', 'Palomas')
@section('content_header_subtitle', 'Editar: ' . $paloma->anilla)

@section('content_body')
    <div class="container-fluid">

        {{-- Mensajes de error --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #0d6efd;">
                <h3 class="card-title">
                    <i class="fas fa-dove text-primary"></i> Editar Paloma
                </h3>
                <div class="card-tools">
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.palomas.show', $paloma) }}" class="btn btn-outline-info"
                            style="background-color: rgba(23, 162, 184, 0.08); border-color: rgba(23, 162, 184, 0.25);"
                            aria-label="Ver paloma" title="Ver paloma">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.palomares.show', $paloma->palomar_id) }}" class="btn btn-outline-secondary"
                            style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                            aria-label="Volver al palomar" title="Volver al palomar">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.palomas.update', $paloma) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Fila 1: Palomar y Anilla --}}
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="palomar_id">
                                    <i class="fas fa-home mr-1 text-primary"></i> Palomar <span class="text-danger">*</span>
                                </label>
                                <select name="palomar_id" id="palomar_id"
                                    class="form-control @error('palomar_id') is-invalid @enderror"
                                    style="border-radius: 50px; padding: 10px 20px;" required>
                                    @foreach ($palomares as $palomar)
                                        <option value="{{ $palomar->id }}"
                                            {{ old('palomar_id', $paloma->palomar_id) == $palomar->id ? 'selected' : '' }}>
                                            {{ $palomar->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('palomar_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="anilla">
                                    <i class="fas fa-hashtag mr-1 text-primary"></i> Anilla <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text" name="anilla" id="anilla"
                                    class="form-control @error('anilla') is-invalid @enderror"
                                    value="{{ old('anilla', $paloma->anilla) }}"
                                    style="border-radius: 50px; padding: 10px 20px;" placeholder="Ej: P-12345" required>
                                @error('anilla')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Nombre y Fecha Nacimiento --}}
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="nombre">
                                    <i class="fas fa-tag mr-1 text-primary"></i> Nombre
                                </label>
                                <input type="text" name="nombre" id="nombre"
                                    class="form-control @error('nombre') is-invalid @enderror"
                                    value="{{ old('nombre', $paloma->nombre) }}"
                                    style="border-radius: 50px; padding: 10px 20px;" placeholder="Nombre de la paloma">
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="fecha_nacimiento">
                                    <i class="fas fa-calendar-alt mr-1 text-primary"></i> Fecha de nacimiento
                                </label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                    class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                    value="{{ old('fecha_nacimiento', optional($paloma->fecha_nacimiento)->format('Y-m-d')) }}"
                                    style="border-radius: 50px; padding: 10px 20px;">
                                @error('fecha_nacimiento')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 3: Sexo y Color --}}
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="sexo">
                                    <i class="fas fa-venus-mars mr-1 text-primary"></i> Sexo
                                </label>
                                <select name="sexo" id="sexo"
                                    class="form-control @error('sexo') is-invalid @enderror"
                                    style="border-radius: 50px; padding: 10px 20px;">
                                    <option value="desconocido"
                                        {{ old('sexo', $paloma->sexo) == 'desconocido' ? 'selected' : '' }}>Desconocido
                                    </option>
                                    <option value="macho" {{ old('sexo', $paloma->sexo) == 'macho' ? 'selected' : '' }}>
                                        Macho</option>
                                    <option value="hembra" {{ old('sexo', $paloma->sexo) == 'hembra' ? 'selected' : '' }}>
                                        Hembra</option>
                                </select>
                                @error('sexo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Estado Sanitario</label>
                            <select name="estado_sanitario" class="form-control">
                                <option value="Bien" {{ $paloma->estado_sanitario === 'Bien' ? 'selected' : '' }}>Bien
                                </option>
                                <option value="Enferma" {{ $paloma->estado_sanitario === 'Enferma' ? 'selected' : '' }}>
                                    Enferma</option>
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="color">
                                    <i class="fas fa-palette mr-1 text-primary"></i> Color
                                </label>
                                <input type="text" name="color" id="color"
                                    class="form-control @error('color') is-invalid @enderror"
                                    value="{{ old('color', $paloma->color) }}"
                                    style="border-radius: 50px; padding: 10px 20px;"
                                    placeholder="Ej: Blanco, Negro, Gris...">
                                @error('color')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 4: Raza y Origen --}}
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="raza">
                                    <i class="fas fa-paw mr-1 text-primary"></i> Raza
                                </label>
                                <input type="text" name="raza" id="raza"
                                    class="form-control @error('raza') is-invalid @enderror"
                                    value="{{ old('raza', $paloma->raza) }}"
                                    style="border-radius: 50px; padding: 10px 20px;"
                                    placeholder="Ej: Colombófila, Raza...">
                                @error('raza')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="origen">
                                    <i class="fas fa-globe-americas mr-1 text-primary"></i> Origen
                                </label>
                                <input type="text" name="origen" id="origen"
                                    class="form-control @error('origen') is-invalid @enderror"
                                    value="{{ old('origen', $paloma->origen) }}"
                                    style="border-radius: 50px; padding: 10px 20px;"
                                    placeholder="Ej: Argentina, Brasil...">
                                @error('origen')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 5: Padre y Madre --}}
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="padre_id">
                                    <i class="fas fa-male mr-1 text-primary"></i> Padre
                                </label>
                                <select name="padre_id" id="padre_id" class="form-control"
                                    style="border-radius: 50px; padding: 10px 20px;">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($palomasDisponibles as $p)
                                        @if ($p->id != $paloma->id)
                                            <option value="{{ $p->id }}"
                                                {{ old('padre_id', $paloma->padre_id) == $p->id ? 'selected' : '' }}>
                                                {{ $p->anilla }} - {{ $p->nombre ?? 'S/N' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('padre_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="madre_id">
                                    <i class="fas fa-female mr-1 text-primary"></i> Madre
                                </label>
                                <select name="madre_id" id="madre_id" class="form-control"
                                    style="border-radius: 50px; padding: 10px 20px;">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($palomasDisponibles as $p)
                                        @if ($p->id != $paloma->id)
                                            <option value="{{ $p->id }}"
                                                {{ old('madre_id', $paloma->madre_id) == $p->id ? 'selected' : '' }}>
                                                {{ $p->anilla }} - {{ $p->nombre ?? 'S/N' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('madre_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 6: Estado y Observaciones --}}
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="estado_id">
                                    <i class="fas fa-circle mr-1 text-primary"></i> Estado <span
                                        class="text-danger">*</span>
                                </label>
                                <select name="estado_id" id="estado_id"
                                    class="form-control @error('estado_id') is-invalid @enderror"
                                    style="border-radius: 50px; padding: 10px 20px;" required>
                                    @foreach ($estados as $estado)
                                        <option value="{{ $estado->id }}"
                                            {{ old('estado_id', $paloma->estado_id) == $estado->id ? 'selected' : '' }}>
                                            {{ $estado->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estado_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                <label for="observaciones">
                                    <i class="fas fa-comment mr-1 text-primary"></i> Observaciones
                                </label>
                                <textarea name="observaciones" id="observaciones" class="form-control"
                                    style="border-radius: 15px; padding: 10px 20px; min-height: 50px; resize: vertical;" rows="2">{{ old('observaciones', $paloma->observaciones) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-4">
                        <div>
                            <button type="submit" class="btn btn-primary"
                                style="border-radius: 50px; padding: 10px 30px; font-weight: 600; box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3); transition: all 0.3s ease;">
                                <i class="fas fa-save mr-2"></i> Actualizar
                            </button>
                            <a href="{{ route('admin.palomares.show', $paloma->palomar_id) }}"
                                class="btn btn-outline-secondary"
                                style="border-radius: 50px; padding: 10px 30px; font-weight: 600; background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                                <i class="fas fa-times mr-2"></i> Cancelar
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('admin.palomas.show', $paloma) }}" class="btn btn-outline-info"
                                style="border-radius: 50px; padding: 10px 30px; font-weight: 600; background-color: rgba(23, 162, 184, 0.08); border-color: rgba(23, 162, 184, 0.25);">
                                <i class="fas fa-eye mr-2"></i> Ver Paloma
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        /* Mejoras responsivas para el formulario */
        .form-control {
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15) !important;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary:hover {
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4) !important;
        }

        select.form-control {
            appearance: auto;
            -webkit-appearance: auto;
            -moz-appearance: auto;
        }

        @media (max-width: 768px) {
            .d-flex.flex-wrap {
                flex-direction: column;
                gap: 10px;
            }

            .d-flex.flex-wrap>div {
                width: 100%;
                text-align: center;
            }

            .d-flex.flex-wrap .btn {
                width: 100%;
                margin: 5px 0 !important;
            }

            .form-control {
                font-size: 0.9rem;
                padding: 8px 15px !important;
            }
        }

        @media (max-width: 576px) {
            .card-header {
                flex-direction: column;
                text-align: center;
            }

            .card-header .card-tools {
                margin-top: 10px;
                width: 100%;
            }

            .card-header .card-tools .btn-group {
                width: 100%;
            }

            .card-header .card-tools .btn-group .btn {
                flex: 1;
                font-size: 0.75rem;
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            // Auto-ocultar alertas después de 5 segundos
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
@endpush
