@extends('layouts.app')

@section('subtitle', 'Inventario - Ubicaciones')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Ubicaciones')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.ubicaciones-catalogo />
</div>
@stop
