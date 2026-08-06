@extends('adminlte::page')

@section('title', 'Jefes de Unidad')

@section('content')
    <x-ops-card title="Jefes de Unidad" icon="user-tie">
        <livewire:admin.jefes-unidad-panel />
    </x-ops-card>
@endsection