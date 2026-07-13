@extends('layouts.app')

@section('subtitle', 'Novedades del día')
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Vista general del día')

@section('content_body')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(!$guardia)
        <div class="alert alert-warning shadow-sm d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle mr-3 fa-lg"></i>
            <div>
                No hay guardia abierta hoy.
                @can('create', App\Models\Guard::class)
                    <a href="{{ route('admin.guardias.create') }}" class="alert-link font-weight-bold ml-1">Abrir guardia del día</a>
                @endcan
            </div>
        </div>
    @else
        {{-- Resumen de la guardia --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-info shadow-sm">
                    <div class="inner">
                        <h4>{{ $guardia->date->format('d/m/Y') }}</h4>
                        <p>Guardia del día</p>
                        @if($guardia->status === 'open')
                            <span class="badge badge-light text-success">Abierta</span>
                        @else
                            <span class="badge badge-light text-warning">Cerrada</span>
                        @endif
                    </div>
                    <div class="icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-warning shadow-sm">
                    <div class="inner">
                        <h5>{{ $guardia->capitan->grade }} {{ $guardia->capitan->name }}</h5>
                        <p>Capitán de Servicio</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-success shadow-sm">
                    <div class="inner">
                        <h5>{{ $guardia->oficial->grade }} {{ $guardia->oficial->name }}</h5>
                        <p>Oficial de Día</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-danger shadow-sm">
                    <div class="inner">
                        <h4>{{ $guardia->novedades->count() }}</h4>
                        <p>Total Novedades</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de novedades --}}
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-eye mr-1"></i> Ver Guardia
            </a>
        </div>
        <livewire:novedades-guardia :guardia="$guardia" :puede-operar-guardia="$puedeOperarGuardia"
            :key="'novedades-guardia-index-' . $guardia->id" />
    @endif
</div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        // Auto-cerrar alertas después de 5 segundos
        $('.alert').delay(5000).slideUp(500);
    });
</script>
@endpush