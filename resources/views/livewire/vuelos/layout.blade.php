@extends('layouts.app')

@section('subtitle', 'Vuelos')
@section('content_header_title', 'Vuelos')
@section('content_header_subtitle', 'Listado general')

@section('content_body')
<div class="container-fluid">
    <livewire:vuelos />
</div>
@stop
