@extends('layouts.app')

@section('subtitle', 'Unidades')
@section('content_header_title', 'Unidades')
@section('content_header_subtitle', 'Listado')

@section('content_body')
<div class="container-fluid">
    <livewire:unidades :edit="request()->integer('edit') ?: null" />
</div>
@stop