@extends('layouts.app')

@section('subtitle', 'Editar Usuario')
@section('content_header_title', 'Usuarios')
@section('content_header_subtitle', 'Editar')

@section('content_body')
    <div class="container-fluid">
        <livewire:admin.user-form :user="$user" />
        <div class="mt-4">
            <livewire:admin.historial-grados-panel :user="$user" />
        </div>
        <div class="mt-4">
            <livewire:admin.historial-estado-panel :user="$user" />
        </div>
        <div class="mt-4">
            <livewire:admin.pase-panel :user="$user" />
        </div>
        <div class="mt-4">
            <livewire:admin.comision-panel :user="$user" />
        </div>
    </div>
@stop
