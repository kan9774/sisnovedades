@extends('layouts.app')

@section('subtitle', 'Inventario - Historial de Entregas')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Historial de Entregas')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.entregas-historial />
</div>
@stop