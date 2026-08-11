@extends('layouts.app')
@section('subtitle', 'Auditoría')
@section('content_header_title', 'Sistema')
@section('content_header_subtitle', 'Log de Actividad')

@section('content_body')
    @livewire('logs')
@stop
