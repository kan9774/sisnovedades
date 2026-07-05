@extends('layouts.app')

@section('subtitle', $documento->titulo)
@section('content_header_title', $documento->titulo)
@section('content_header_subtitle', $documento->categoria->nombre)

@section('content_body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $documento->titulo }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.documentos.download', $documento) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-download"></i> Descargar
                </a>
                <a href="{{ route('admin.documentos.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <iframe src="{{ Storage::url($documento->archivo_path) }}"
                    width="100%" height="800px" style="border:none;"></iframe>
        </div>
    </div>
</div>
@stop