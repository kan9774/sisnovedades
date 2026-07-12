@extends('layouts.app')
@section('subtitle', 'Novedades del día')
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Vista general del día')

@section('content_body')
    @livewire('documentos')
@stop