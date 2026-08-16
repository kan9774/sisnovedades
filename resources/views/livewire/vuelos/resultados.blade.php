@extends('layouts.app')

@section('subtitle', 'Cargar Resultados')
@section('content_header_title', 'Vuelos')
@section('content_header_subtitle', 'Cargar Resultados')

@section('content_body')
<div class="container-fluid">
    <livewire:vuelos.vuelos-resultados :vuelo-id="$vueloId" />
</div>
@stop
