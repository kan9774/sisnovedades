@extends('layouts.app')

@section('subtitle', 'Unidades por Módulo')
@section('content_header_title', 'Unidades por Módulo')
@section('content_header_subtitle', 'Listas curadas')

@section('content_body')
<div class="container-fluid">
    <livewire:admin.unidades-modulos />
</div>
@stop
