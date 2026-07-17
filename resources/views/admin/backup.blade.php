@extends('layouts.app')

@section('subtitle', 'Backups')
@section('content_header_title', 'Gestión de Backups')
@section('content_header_subtitle', 'Respaldo de base de datos')

@section('content_body')
<div class="container-fluid">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    {{-- Panel Livewire --}}
    <livewire:backup-manager />

</div>
@endsection
