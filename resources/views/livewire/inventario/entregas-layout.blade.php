@extends('layouts.app')

@section('subtitle', 'Inventario - Entregas')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Entregas y devoluciones')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.entregas-inventario />
</div>
@stop