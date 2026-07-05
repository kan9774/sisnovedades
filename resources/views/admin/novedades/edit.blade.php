@extends('layouts.app')

@section('subtitle', 'Editar Novedad')
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Editar')

@section('content_body')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit"></i> Editar Novedad #{{ $novedad->id }}
            </h3>
            <div class="card-tools ml-2">
                <a href="{{ route('admin.guardias.novedades.show', [$guardia, $novedad]) }}"
                   class="btn btn-outline-secondary btn-sm mr-1"
                        style="background-color: rgba(108, 117, 125, 0.08);" aria-label="Volver a la guardia">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> Por favor, corrige los siguientes errores:
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('admin.guardias.novedades.update', [$guardia, $novedad]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Tipo</label>
                            <select name="type" class="form-control shadow-sm @error('type') is-invalid @enderror" required>
                                @foreach(['Radio','Fax','Correo Electrónico'] as $tipo)
                                    <option value="{{ $tipo }}" {{ old('type', $novedad->type) == $tipo ? 'selected' : '' }}>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Dirección</label>
                            <select name="direction" class="form-control shadow-sm @error('direction') is-invalid @enderror" required>
                                @foreach(['Recibido','Expedido'] as $dir)
                                    <option value="{{ $dir }}" {{ old('direction', $novedad->direction) == $dir ? 'selected' : '' }}>
                                        {{ $dir }}
                                    </option>
                                @endforeach
                            </select>
                            @error('direction')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Clasificación</label>
                            <select name="clasification" class="form-control shadow-sm @error('clasification') is-invalid @enderror" required>
                                @foreach(['Rutinario','Prioritario','Urgente','Destello'] as $clas)
                                    <option value="{{ $clas }}" {{ old('clasification', $novedad->clasification) == $clas ? 'selected' : '' }}>
                                        {{ $clas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('clasification')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Número</label>
                            <input type="text" name="number"
                                   class="form-control shadow-sm @error('number') is-invalid @enderror"
                                   value="{{ old('number', $novedad->number) }}" required>
                            @error('number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Hora</label>
                            <input type="time" name="time"
                                   class="form-control shadow-sm @error('time') is-invalid @enderror"
                                   value="{{ old('time', $novedad->time?->format('H:i')) }}" required>
                            @error('time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Oficina</label>
                            <input type="text" name="office"
                                   class="form-control shadow-sm @error('office') is-invalid @enderror"
                                   value="{{ old('office', $novedad->office) }}">
                            @error('office')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Asunto</label>
                    <input type="text" name="affair"
                           class="form-control shadow-sm @error('affair') is-invalid @enderror"
                           value="{{ old('affair', $novedad->affair) }}">
                    @error('affair')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Texto</label>
                    <textarea name="text" rows="5"
                              class="form-control shadow-sm @error('text') is-invalid @enderror"
                              required>{{ old('text', $novedad->text) }}</textarea>
                    @error('text')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <!-- Botones de acción -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <a href="{{ route('admin.guardias.novedades.show', [$guardia, $novedad]) }}"
                       class="btn btn-outline-secondary"
                       style="background-color: rgba(108, 117, 125, 0.08);"
                       aria-label="Cancelar edición">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary"
                            style="background-color: rgba(0, 123, 255, 0.1); border-color: #007bff; color: #007bff;"
                            aria-label="Guardar cambios">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop