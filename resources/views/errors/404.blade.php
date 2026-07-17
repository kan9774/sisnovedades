@extends('layouts.app')

@section('title', '404 - Página no encontrada')
@section('subtitle', 'Error 404')
@section('content_header_title', 'Página No Encontrada')
@section('content_header_subtitle', 'El recurso que buscás no existe')

@section('content_body')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h2 class="headline text-danger" style="font-size: 8rem; margin: 0;">
                    <i class="fas fa-search-minus"></i> 404
                </h2>
                <div class="error-content">
                    <h3>
                        <i class="fas fa-exclamation-circle text-danger"></i>
                        ¡Página no encontrada!
                    </h3>
                    <p>
                        La página que estás buscando no existe, fue eliminada, o el enlace está mal escrito.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.index') }}" class="btn btn-danger">
                            <i class="fas fa-home mr-1"></i> Volver al Inicio
                        </a>
                        <a href="javascript:history.back()" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
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
