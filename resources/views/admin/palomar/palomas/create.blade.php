@extends('layouts.app')

@section('subtitle', 'Nueva Paloma')
@section('content_header_title', 'Palomas')
@section('content_header_subtitle', 'Crear')

@section('content_body')
    <div class="container-fluid">

        {{-- Mensajes de error (Sintaxis Bootstrap 4.6) --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                <ul class="mb-0 pl-3 d-inline-block">
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
                    <i class="fa-solid fa-dove text-primary"></i> Nueva Paloma
                </h3>
                <div class="card-tools">
                    @php $palomarId = request('palomar_id') ?? old('palomar_id'); @endphp
                    <a href="{{ $palomarId ? route('admin.palomares.show', $palomarId) : route('admin.palomas.index') }}"
                        class="btn btn-outline-secondary btn-sm"
                        style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                        aria-label="Cancelar y volver">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.palomas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="palomar_id" value="{{ $palomarId }}">

                    {{-- Bloque 1: Identificación Básica --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="anilla">
                                    <i class="fa-solid fa-hashtag text-muted mr-1"></i> Anilla <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="anilla" id="anilla"
                                    class="form-control @error('anilla') is-invalid @enderror" value="{{ old('anilla') }}"
                                    placeholder="Ej: P-123" required>
                                @error('anilla')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">
                                    <i class="fa-solid fa-tag text-muted mr-1"></i> Nombre
                                </label>
                                <input type="text" name="nombre" id="nombre"
                                    class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}"
                                    placeholder="Ej: Lucero">
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 2: Características Físicas Básicas --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sexo">
                                    <i class="fa-solid fa-venus-mars text-muted mr-1"></i> Sexo
                                </label>
                                <select name="sexo" id="sexo"
                                    class="form-control @error('sexo') is-invalid @enderror">
                                    <option value="desconocido" {{ old('sexo') == 'desconocido' ? 'selected' : '' }}>
                                        Desconocido</option>
                                    <option value="macho" {{ old('sexo') == 'macho' ? 'selected' : '' }}>Macho</option>
                                    <option value="hembra" {{ old('sexo') == 'hembra' ? 'selected' : '' }}>Hembra</option>
                                </select>
                                @error('sexo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="color">
                                    <i class="fa-solid fa-palette text-muted mr-1"></i> Color
                                </label>
                                <input type="text" name="color" id="color"
                                    class="form-control @error('color') is-invalid @enderror" value="{{ old('color') }}"
                                    placeholder="Ej: Blanco, Negro, Gris">
                                @error('color')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 3: Nacimiento y Raza --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_nacimiento">
                                    <i class="fa-solid fa-calendar-days text-muted mr-1"></i> Fecha de nacimiento
                                </label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                    class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                    value="{{ old('fecha_nacimiento') }}">
                                @error('fecha_nacimiento')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="raza">
                                    <i class="fa-solid fa-paw text-muted mr-1"></i> Raza
                                </label>
                                <input type="text" name="raza" id="raza"
                                    class="form-control @error('raza') is-invalid @enderror" value="{{ old('raza') }}"
                                    placeholder="Ej: Stassart, Janssen, Delbar">
                                @error('raza')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 4: Origen y Genealogía --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="origen">
                                    <i class="fa-solid fa-earth-americas text-muted mr-1"></i> Origen
                                </label>
                                <input type="text" name="origen" id="origen"
                                    class="form-control @error('origen') is-invalid @enderror"
                                    value="{{ old('origen') }}" placeholder="Ej: Argentina, Uruguay">
                                @error('origen')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="padre_id">
                                    <i class="fa-solid fa-mars text-muted mr-1"></i> Padre
                                </label>
                                <select name="padre_id" id="padre_id"
                                    class="form-control @error('padre_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($palomasDisponibles as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('padre_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->anilla }} - {{ $p->nombre ?? 'S/N' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('padre_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="madre_id">
                                    <i class="fa-solid fa-venus text-muted mr-1"></i> Madre
                                </label>
                                <select name="madre_id" id="madre_id"
                                    class="form-control @error('madre_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($palomasDisponibles as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('madre_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->anilla }} - {{ $p->nombre ?? 'S/N' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('madre_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 5: Estado del Sistema y Salud --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado_id">
                                    <i class="fa-solid fa-circle text-muted mr-1" style="font-size: 0.8rem;"></i> Estado <span
                                        class="text-danger">*</span>
                                </label>
                                <select name="estado_id" id="estado_id"
                                    class="form-control @error('estado_id') is-invalid @enderror" required>
                                    @foreach ($estados as $estado)
                                        <option value="{{ $estado->id }}"
                                            {{ old('estado_id') == $estado->id ? 'selected' : '' }}>
                                            {{ $estado->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('estado_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado_sanitario">
                                    <i class="fa-solid fa-heart-pulse text-muted mr-1"></i> Estado Sanitario
                                </label>
                                <select name="estado_sanitario" id="estado_sanitario" class="form-control">
                                    <option value="Bien" selected>Bien</option>
                                    <option value="Enferma">Enferma</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque 6: Observaciones --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="observaciones">
                                    <i class="fa-solid fa-comment text-muted mr-1"></i> Observaciones
                                </label>
                                <textarea name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror"
                                    rows="2" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Botones (Sintaxis d-flex flex-wrap justify-content-between align-items-center compatible) --}}
                    <div class="row mt-3">
                        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-outline-primary"
                                    style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>
                                <a href="{{ $palomarId ? route('admin.palomares.show', $palomarId) : route('admin.palomas.index') }}"
                                    class="btn btn-outline-secondary"
                                    style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                                    <i class="fa-solid fa-xmark"></i> Cancelar
                                </a>
                            </div>
                            <span class="text-muted small">
                                <i class="fa-solid fa-asterisk text-danger mr-1" style="font-size: 0.6rem;"></i> Campos
                                obligatorios
                            </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        /* Mejoras visuales para los campos */
        .form-control {
            border-radius: 8px !important;
            border: 2px solid #e9ecef !important;
            transition: all 0.3s ease !important;
            padding: 10px 15px !important;
            height: auto !important;
        }

        .form-control:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15) !important;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15) !important;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25) !important;
        }

        select.form-control {
            appearance: auto;
            -webkit-appearance: auto;
            -moz-appearance: auto;
        }

        /* Botones con estilo soft */
        .btn-outline-primary,
        .btn-outline-secondary {
            border-radius: 50px !important;
            padding: 8px 20px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }

        .btn-outline-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3) !important;
        }

        .btn-outline-secondary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2) !important;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .card-body {
                padding: 20px 15px !important;
            }

            .form-control {
                font-size: 0.9rem !important;
                padding: 8px 12px !important;
            }

            .btn-outline-primary,
            .btn-outline-secondary {
                font-size: 0.85rem !important;
                padding: 6px 16px !important;
            }

            .card-header .btn {
                font-size: 0.75rem !important;
                padding: 4px 12px !important;
            }

            .card-header h3 {
                font-size: 1.1rem !important;
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