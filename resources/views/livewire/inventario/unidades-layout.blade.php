@extends('layouts.app')

@section('subtitle', 'Inventario - Unidades Individuales')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Unidades Individuales')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.unidades-individuales />
</div>
@stop
