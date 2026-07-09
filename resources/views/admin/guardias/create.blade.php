@extends('layouts.app')

@section('subtitle', 'Nueva Guardia')
@section('content_header_title', 'Guardias')
@section('content_header_subtitle', 'Nueva Guardia')

@section('content_body')
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-door-open"></i> Abrir guardia del día
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

                <form action="{{ route('admin.guardias.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', today()->format('Y-m-d')) }}" required>
                        @error('date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Capitán de Servicio</label>
                        <select name="captain_id" class="form-control @error('captain_id') is-invalid @enderror" required>
                            <option value="" disabled selected>-- Seleccionar Capitán --</option>
                            @foreach ($capitanes as $capitan)
                                <option value="{{ $capitan->id }}"
                                    {{ old('captain_id') == $capitan->id ? 'selected' : '' }}>
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
                            <option value="" disabled selected>-- Seleccionar Oficial --</option>
                            @foreach ($oficiales as $oficial)
                                <option value="{{ $oficial->id }}"
                                    {{ old('oficer_id') == $oficial->id ? 'selected' : '' }}>
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
                        @if (auth()->user()->isEscribiente())
                            <input type="text" class="form-control"
                                value="{{ auth()->user()->grade }} {{ auth()->user()->name }} {{ auth()->user()->last_name }}"
                                disabled>
                            <input type="hidden" name="escribiente_id" value="{{ auth()->id() }}">
                        @else
                            <select name="escribiente_id" class="form-control @error('escribiente_id') is-invalid @enderror"
                                required>
                                <option value="" disabled selected>-- Seleccionar Escribiente --</option>
                                @foreach ($escribientes as $escribiente)
                                    <option value="{{ $escribiente->id }}"
                                        {{ old('escribiente_id') == $escribiente->id ? 'selected' : '' }}>
                                        {{ $escribiente->grade }} {{ $escribiente->name }} {{ $escribiente->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('escribiente_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Notas <small class="text-muted">(opcional)</small></label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.guardias.index') }}" class="btn btn-outline-secondary btn-sm"
                            style="background-color: rgba(108, 117, 125, 0.08);" aria-label="Volver a la lista de guardias">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08);" aria-label="Abrir guardia">
                            <i class="fas fa-save"></i> Abrir Guardia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-multiple').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccionar escribientes',
                allowClear: true
            });
        });
    </script>
@endpush
