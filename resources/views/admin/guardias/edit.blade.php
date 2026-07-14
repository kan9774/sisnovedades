@extends('layouts.app')

@section('subtitle', 'Editar Guardia')
@section('content_header_title', 'Guardias')
@section('content_header_subtitle', 'Editar guardia del ' . $guardia->date->format('d/m/Y'))

@section('content_body')
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i> Editar guardia del {{ $guardia->date->format('d/m/Y') }}
                </h3>
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

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Solo el capitán, el oficial de día o el/la escribiente asignados a esta guardia
                    (o un administrador) pueden editarla. El cambio queda registrado en el log de auditoría.
                </div>

                <form action="{{ route('admin.guardias.update', $guardia) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="text" class="form-control" value="{{ $guardia->date->format('d/m/Y') }}" disabled>
                    </div>

                    <div class="form-group">
                        <label>Capitán de Servicio</label>
                        <select name="captain_id" class="form-control @error('captain_id') is-invalid @enderror" required>
                            <option value="" disabled>-- Seleccionar Capitán --</option>
                            @foreach ($capitanes as $capitan)
                                <option value="{{ $capitan->id }}"
                                    {{ old('captain_id', $guardia->captain_id) == $capitan->id ? 'selected' : '' }}>
                                    {{ $capitan->grade }} {{ $capitan->name }} {{ $capitan->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('captain_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Oficial de Día</label>
                        <select name="oficer_id" class="form-control @error('oficer_id') is-invalid @enderror" required>
                            <option value="" disabled>-- Seleccionar Oficial --</option>
                            @foreach ($oficiales as $oficial)
                                <option value="{{ $oficial->id }}"
                                    {{ old('oficer_id', $guardia->oficer_id) == $oficial->id ? 'selected' : '' }}>
                                    {{ $oficial->grade }} {{ $oficial->name }} {{ $oficial->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('oficer_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Escribiente</label>
                        <select name="escribiente_id" class="form-control @error('escribiente_id') is-invalid @enderror"
                            required>
                            <option value="" disabled>-- Seleccionar Escribiente --</option>
                            @foreach ($escribientes as $escribiente)
                                <option value="{{ $escribiente->id }}"
                                    {{ old('escribiente_id', optional($guardia->escribiente->first())->id) == $escribiente->id ? 'selected' : '' }}>
                                    {{ $escribiente->grade }} {{ $escribiente->name }} {{ $escribiente->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('escribiente_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Si otra persona debe tomar la guardia en tu lugar, seleccionala acá.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Notas <small class="text-muted">(opcional)</small></label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $guardia->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-outline-secondary btn-sm"
                            style="background-color: rgba(108, 117, 125, 0.08);" aria-label="Cancelar y volver">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08);" aria-label="Guardar cambios">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop