@extends('layouts.app')

@section('subtitle', 'Inventario - Movimientos')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Movimientos')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.movimientos-inventario />
</div>
@stop
