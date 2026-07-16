@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard de Administración</h1>
@stop

@section('content')
    <livewire:admin-dashboard />
@stop

@push('js')
    <script src="{{ asset('vendor/livewire/livewire/dist/livewire.min.js') }}" data-turbo-eval="false" data-turbolinks-eval="false"></script>
@endpush
