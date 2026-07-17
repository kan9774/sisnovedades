@extends('layouts.app')

@section('title', '419 - Sesión expirada')
@section('subtitle', 'Error 419')
@section('content_header_title', 'Sesión Expirada')
@section('content_header_subtitle', 'Tu sesión caducó por inactividad')

@section('content_body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h2 class="headline text-info" style="font-size: 8rem; margin: 0;">
                    <i class="fas fa-clock"></i> 419
                </h2>
                <div class="error-content">
                    <h3>
                        <i class="fas fa-hourglass-half text-info"></i>
                        ¡Tu sesión expiró!
                    </h3>
                    <p>
                        Tu sesión caducó por inactividad o el token de seguridad expiró.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="btn btn-info">
                            <i class="fas fa-sign-in-alt mr-1"></i> Iniciar Sesión
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-home mr-1"></i> Ir al Inicio
                        </a>
                    </div>
                    <div class="mt-3 text-muted small">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Para evitar que esto suceda, no dejes la pestaña abierta sin actividad por mucho tiempo.
                        </p>
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
