@extends('layouts.app')

@section('subtitle', 'Conductores')
@section('content_header_title', 'Conductores')
@section('content_header_subtitle', 'Listado')

@section('content_body')
<div class="container-fluid">
    <livewire:conductores />
</div>
@stop
