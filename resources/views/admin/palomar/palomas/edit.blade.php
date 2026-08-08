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

        <x-ops-card icon="dove" title="Editar Paloma">
            <x-slot:actions>
                <a href="{{ route('admin.palomas.show', $paloma) }}" class="btn-ops btn-ops-info btn-ops-icon"
                    aria-label="Ver paloma" title="Ver paloma">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('admin.palomares.show', $paloma->palomar_id) }}" class="btn-ops btn-ops-secondary"
                    aria-label="Volver al palomar" title="Volver al palomar">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </x-slot:actions>

            <form action="{{ route('admin.palomas.update', $paloma) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Fila 1: Palomar y Anilla --}}
                <div class="row">
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="palomar_id">
                                <i class="fas fa-home mr-1 text-primary"></i> Palomar <span class="text-danger">*</span>
                            </label>
                            <select name="palomar_id" id="palomar_id"
                                class="form-control @error('palomar_id') is-invalid @enderror" required>
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
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="anilla">
                                <i class="fas fa-hashtag mr-1 text-primary"></i> Anilla <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="anilla" id="anilla"
                                class="form-control @error('anilla') is-invalid @enderror"
                                value="{{ old('anilla', $paloma->anilla) }}" placeholder="Ej: P-12345" required>
                            @error('anilla')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="estado_id">
                                <i class="fas fa-circle mr-1 text-primary"></i> Estado <span class="text-danger">*</span>
                            </label>
                            <select name="estado_id" id="estado_id"
                                class="form-control @error('estado_id') is-invalid @enderror" required>
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
                </div>

                {{-- Fila 2: Nombre y Fecha Nacimiento --}}
                <div class="row">
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="nombre">
                                <i class="fas fa-tag mr-1 text-primary"></i> Nombre
                            </label>
                            <input type="text" name="nombre" id="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre', $paloma->nombre) }}" placeholder="Nombre de la paloma">
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="fecha_nacimiento">
                                <i class="fas fa-calendar-alt mr-1 text-primary"></i> Fecha de nacimiento
                            </label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                                class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                value="{{ old('fecha_nacimiento', optional($paloma->fecha_nacimiento)->format('Y-m-d')) }}">
                            @error('fecha_nacimiento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="sexo">
                                <i class="fas fa-venus-mars mr-1 text-primary"></i> Sexo
                            </label>
                            <select name="sexo" id="sexo" class="form-control @error('sexo') is-invalid @enderror">
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
                </div>

                {{-- Fila 3: Sexo y Color --}}
                <div class="row">
                    <div class="col-md-4">
                        <label>Estado Sanitario</label>
                        <select name="estado_sanitario" class="form-control">
                            <option value="Bien" {{ $paloma->estado_sanitario === 'Bien' ? 'selected' : '' }}>Bien
                            </option>
                            <option value="Enferma" {{ $paloma->estado_sanitario === 'Enferma' ? 'selected' : '' }}>
                                Enferma</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="color">
                                <i class="fas fa-palette mr-1 text-primary"></i> Color
                            </label>
                            <input type="text" name="color" id="color"
                                class="form-control @error('color') is-invalid @enderror"
                                value="{{ old('color', $paloma->color) }}" placeholder="Ej: Blanco, Negro, Gris...">
                            @error('color')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="raza">
                                <i class="fas fa-paw mr-1 text-primary"></i> Raza
                            </label>
                            <input type="text" name="raza" id="raza"
                                class="form-control @error('raza') is-invalid @enderror"
                                value="{{ old('raza', $paloma->raza) }}" placeholder="Ej: Colombófila, Raza...">
                            @error('raza')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                {{-- Fila 5: Padre y Madre --}}
                <div class="row">
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="origen">
                                <i class="fas fa-globe-americas mr-1 text-primary"></i> Origen
                            </label>
                            <input type="text" name="origen" id="origen"
                                class="form-control @error('origen') is-invalid @enderror"
                                value="{{ old('origen', $paloma->origen) }}" placeholder="Ej: Argentina, Brasil...">
                            @error('origen')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="padre_id">
                                <i class="fas fa-male mr-1 text-primary"></i> Padre
                            </label>
                            <select name="padre_id" id="padre_id" class="form-control">
                                <option value="">Seleccionar...</option>
                                @foreach ($palomasDisponibles as $p)
                                    @if ($p->id != $paloma->id && $p->sexo === 'macho')
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
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="madre_id">
                                <i class="fas fa-female mr-1 text-primary"></i> Madre
                            </label>
                            <select name="madre_id" id="madre_id" class="form-control">
                                <option value="">Seleccionar...</option>
                                @foreach ($palomasDisponibles as $p)
                                    @if ($p->id != $paloma->id && $p->sexo === 'hembra')
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

                {{-- Fila 4: Estado y Observaciones --}}
                <div class="row">
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">
                            <label for="observaciones">
                                <i class="fas fa-comment mr-1 text-primary"></i> Observaciones
                            </label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2">{{ old('observaciones', $paloma->observaciones) }}</textarea>
                        </div>
                    </div>
                </div>
                {{-- Botones --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-4">
                    <div>
                        <button type="submit" class="btn-ops btn-ops-primary">
                            <i class="fas fa-save mr-2"></i> Actualizar
                        </button>
                        <a href="{{ route('admin.palomares.show', $paloma->palomar_id) }}"
                            class="btn-ops footer-btn btn-ops-secondary">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('admin.palomas.show', $paloma) }}" class="btn-ops btn-ops-info">
                            <i class="fas fa-eye mr-2"></i> Ver Paloma
                        </a>
                    </div>
                </div>
            </form>
        </x-ops-card>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            // Auto-ocultar alertas después de 5 segundos
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
@endpush
