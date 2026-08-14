@extends('layouts.app')

@section('subtitle', 'Palomas')
@section('content_header_title', 'Palomas')
@section('content_header_subtitle', 'Listado general')

@section('content_body')
<div class="container-fluid">
    <livewire:palomas />
</div>
@stop
