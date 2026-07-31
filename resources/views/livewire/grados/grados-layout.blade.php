{{-- resources/views/livewire/grado/grados-layout.blade.php --}}
@extends('layouts.app')

@section('subtitle', 'Grados')
@section('content_header_title', 'Grados')
@section('content_header_subtitle', 'Catálogo')

@section('content_body')
<div class="container-fluid">
    <livewire:grado.grados-catalogo />
</div>
@stop