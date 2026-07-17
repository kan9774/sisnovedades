@extends('layouts.app')

@section('title', '403 - No autorizado')
@section('subtitle', 'Error 403')
@section('content_header_title', 'Acceso Denegado')
@section('content_header_subtitle', 'No tenés permisos para acceder a este recurso')

@section('content_body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h2 class="headline text-warning" style="font-size: 8rem; margin: 0;">
                    <i class="fas fa-lock"></i> 403
                </h2>
                <div class="error-content">
                    <h3>
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        ¡No tenés permisos!
                    </h3>
                    <p>
                        No podés acceder a esta página. Necesitás permisos adecuados o iniciar sesión.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.index') }}" class="btn btn-warning">
                            <i class="fas fa-home mr-1"></i> Volver al Inicio
                        </a>
                        @auth
                        <a href="javascript:history.back()" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                        @endauth
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
