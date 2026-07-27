@extends('layouts.app')

@section('subtitle', 'Inventario - Tallas')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Tallas')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.tallas-catalogo />
</div>
@stop