@extends('layouts.app')

@section('subtitle', 'Inventario - Categorías')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Categorías')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.categorias-catalogo />
</div>
@stop
