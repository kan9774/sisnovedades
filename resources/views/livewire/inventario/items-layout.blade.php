@extends('layouts.app')

@section('subtitle', 'Inventario - Catálogo de Ítems')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Catálogo de Ítems')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.items-catalogo />
</div>
@stop
