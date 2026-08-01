@extends('layouts.app')

@section('subtitle', 'Editar Usuario')
@section('content_header_title', 'Usuarios')
@section('content_header_subtitle', 'Editar')

@section('content_body')
    <div class="container-fluid">
        <livewire:admin.user-form :user="$user" />
    </div>
@stop