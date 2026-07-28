@extends('layouts.app')

@section('subtitle', 'Inventario - Vencidos en Terceros')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Vencidos en Terceros')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.vencidos-en-terceros />
</div>
@stop