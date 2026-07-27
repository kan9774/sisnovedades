@extends('layouts.app')

@section('subtitle', 'Inventario - Proveedores')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Proveedores')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.proveedores-catalogo />
</div>
@stop