@extends('layouts.app')

@section('subtitle', 'Estados de Palomas')
@section('content_header_title', 'Estados')
@section('content_header_subtitle', 'Catálogo')

@section('content_body')
<div class="container-fluid">
    <livewire:estados-paloma />
</div>
@stop
