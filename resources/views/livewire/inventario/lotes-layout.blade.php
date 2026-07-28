@extends('layouts.app')

@section('subtitle', 'Inventario - Depósito General')
@section('content_header_title', 'Inventario')
@section('content_header_subtitle', 'Depósito General')

@section('content_body')
<div class="container-fluid">
    <livewire:inventario.lotes-stock />
</div>
@stop