@extends('layouts.app')

@section('subtitle', 'Apoyos S-4')
@section('content_header_title', 'Apoyos S-4')
@section('content_header_subtitle', 'Listado')

@section('content_body')
<div class="container-fluid">
    <livewire:apoyos />
</div>
@stop
