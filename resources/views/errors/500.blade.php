@extends('layouts.app')

@section('title', '500 - Error interno')
@section('subtitle', 'Error 500')
@section('content_header_title', 'Error Interno del Servidor')
@section('content_header_subtitle', 'Algo salió mal en el servidor')

@section('content_body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h2 class="headline text-primary" style="font-size: 8rem; margin: 0;">
                    <i class="fas fa-server"></i> 500
                </h2>
                <div class="error-content">
                    <h3>
                        <i class="fas fa-exclamation-triangle text-primary"></i>
                        ¡Error interno del servidor!
                    </h3>
                    <p>
                        Ocurrió un error inesperado. El equipo técnico ya fue notificado.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.index') }}" class="btn btn-primary">
                            <i class="fas fa-home mr-1"></i> Volver al Inicio
                        </a>
                        <a href="javascript:history.back()" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>
                    <div class="mt-3 text-muted small">
                        <p>Si el problema persiste, contactá al administrador del sistema.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .error-page {
        margin-top: 50px;
    }
    .error-page > .headline {
        float: left;
        font-size: 100px;
        font-weight: 300;
    }
    .error-page > .error-content {
        display: block;
        margin-left: 170px;
    }
    @media (max-width: 768px) {
        .error-page > .headline {
            float: none;
            text-align: center;
        }
        .error-page > .error-content {
            margin-left: 0;
        }
    }
</style>
@endpush
