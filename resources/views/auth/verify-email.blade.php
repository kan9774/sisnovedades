@extends('layouts.guest')

@section('title', 'Verificar email')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Verificar tu correo electrónico')

@section('content')
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success guest-alert alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            Se envió un nuevo enlace de verificación a tu correo electrónico.
        </div>
    @endif

    <p class="text-center guest-link-muted mb-4">
        Antes de continuar, revisá tu correo electrónico y hacé click en el
        enlace de verificación que te enviamos. Si no lo recibiste, podés
        solicitar otro.
    </p>

    <form action="{{ route('verification.send') }}" method="POST">
        @csrf
        <button type="submit" class="guest-btn-primary">
            <i class="fas fa-paper-plane mr-2"></i> Reenviar enlace de verificación
        </button>
    </form>

    <div class="text-center mt-3">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="guest-link-muted" style="background:none;border:none;padding:0;cursor:pointer;">
                <i class="fas fa-sign-out-alt mr-1"></i> Cerrar sesión
            </button>
        </form>
    </div>
@stop

@section('footer')
    <a href="{{ route('home') }}" class="guest-link-muted">
        <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
    </a>
@stop