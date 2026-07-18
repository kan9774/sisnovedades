@extends('layouts.app')

@section('content_header_title', 'Destinatarios')
@section('content_header_subtitle', 'Grupos para enviar la guardia por PDF')

@section('content_body')
    @livewire('pdf-destinatarios')
@endsection
