@extends('layouts.app')

@section('subtitle', 'Nuevo Usuario')
@section('content_header_title', 'Usuarios')
@section('content_header_subtitle', 'Nuevo')

@section('content_body')
    <div class="container-fluid">
        <livewire:admin.user-form />
    </div>
@stop