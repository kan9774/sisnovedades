@extends('layouts.app')

@section('title', '419 - Sesión expirada')
@section('subtitle', 'Error 419')

@push('css')
<style>
    .error-ops-panel-overlay {
        display: block;
        position: fixed;
        inset: 0;
        z-index: 1060;
        background: #f4f5f7;
        animation: opsPanelFadeIn .16s ease-out;
    }

    .error-ops-panel {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        min-height: 100vh;
    }

    .error-ops-panel__form {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .error-ops-panel__header {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.75rem;
        background: linear-gradient(135deg, #0B2545 0%, #0F3460 100%);
        border-bottom: 4px solid #FFD200;
    }

    .error-ops-panel__eyebrow {
        display: block;
        color: #FFD200;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .error-ops-panel__title {
        color: #fff;
        margin: 0;
        font-weight: 600;
    }

    .error-ops-panel__close {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        border-radius: 6px;
        width: 38px;
        height: 38px;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, border-color .15s;
        text-decoration: none;
    }

    .error-ops-panel__close:hover {
        background: rgba(255, 210, 0, 0.18);
        border-color: #FFD200;
        color: #FFD200;
        text-decoration: none;
    }

    .error-ops-panel__body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 2rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .error-ops-panel__content {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px;
        padding: 3rem 2.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        text-align: center;
        width: 100%;
    }

    .error-ops-panel__footer {
        flex: 0 0 auto;
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        padding: 1rem 1.75rem;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.04);
    }

    .error-icon-wrapper {
        font-size: 4.5rem;
        color: #FFD200;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .error-icon-wrapper .error-code {
        font-size: 3.5rem;
        font-weight: 700;
        color: #0B2545;
    }

    .error-icon-wrapper .error-icon {
        font-size: 3rem;
        color: #FFD200;
    }

    .error-message-box {
        background: #FFF8E7;
        border-left: 4px solid #FFD200;
        padding: 1.25rem 1.5rem;
        border-radius: 6px;
        margin: 1.5rem 0;
        text-align: left;
    }

    .error-message-box i {
        color: #FFD200;
        margin-right: 0.75rem;
    }

    .error-message-box .error-message-text {
        font-weight: 500;
        color: #0B2545;
        font-size: 1.05rem;
    }

    .error-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 2rem;
    }

    .btn-ops-primary {
        background: linear-gradient(135deg, #0B2545 0%, #0F3460 100%);
        border: none;
        color: #fff;
        padding: 0.6rem 1.8rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all .15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-ops-primary:hover {
        background: linear-gradient(135deg, #0F3460 0%, #1a4a7a 100%);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(11, 37, 69, 0.25);
        text-decoration: none;
    }

    .btn-ops-secondary {
        background: #f1f3f5;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 0.6rem 1.8rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all .15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-ops-secondary:hover {
        background: #e5e7eb;
        border-color: #9ca3af;
        color: #111827;
        text-decoration: none;
    }

    .btn-ops-warning {
        background: #FFD200;
        border: none;
        color: #0B2545;
        padding: 0.6rem 1.8rem;
        border-radius: 6px;
        font-weight: 600;
        transition: all .15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-ops-warning:hover {
        background: #f0c400;
        color: #0B2545;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 210, 0, 0.4);
        text-decoration: none;
    }

    @keyframes opsPanelFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    body.ops-panel-open {
        overflow: hidden;
    }

    .error-tips {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-top: 1.5rem;
        text-align: left;
    }

    .error-tips i {
        color: #FFD200;
        margin-right: 0.5rem;
    }

    .error-tips li {
        color: #6b7280;
        font-size: 0.9rem;
        padding: 0.25rem 0;
        list-style: none;
    }

    .error-tips li:before {
        content: "•";
        color: #FFD200;
        font-weight: bold;
        display: inline-block;
        width: 1.2rem;
        margin-left: -1.2rem;
    }

    @media (max-width: 768px) {
        .error-ops-panel__content {
            padding: 2rem 1.5rem;
        }
        .error-icon-wrapper {
            font-size: 3rem;
        }
        .error-icon-wrapper .error-code {
            font-size: 2.5rem;
        }
        .error-actions {
            flex-direction: column;
        }
        .error-actions .btn-ops-primary,
        .error-actions .btn-ops-secondary,
        .error-actions .btn-ops-warning {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content_body')
<div class="error-ops-panel-overlay">
    <div class="error-ops-panel">
        <div class="error-ops-panel__form">
            {{-- HEADER --}}
            <header class="error-ops-panel__header">
                <div>
                    <span class="error-ops-panel__eyebrow">Error de autenticación</span>
                    <h1 class="error-ops-panel__title">Sesión Expirada</h1>
                </div>
                <a href="{{ route('home') }}" class="error-ops-panel__close" title="Ir al inicio">
                    <i class="fas fa-times"></i>
                </a>
            </header>

            {{-- BODY --}}
            <div class="error-ops-panel__body">
                <div class="error-ops-panel__content">
                    <div class="error-icon-wrapper">
                        <i class="fas fa-clock error-icon"></i>
                        <span class="error-code">419</span>
                    </div>

                    <h2 style="color: #0B2545; font-weight: 600; margin-bottom: 0.5rem;">
                        {{ $exception->getMessage() ?: '¡Tu sesión expiró!' }}
                    </h2>

                    @if($exception->getMessage())
                        <div class="error-message-box">
                            <div class="error-message-text">
                                <i class="fas fa-info-circle"></i>
                                {{ $exception->getMessage() }}
                            </div>
                        </div>
                    @else
                        <p style="color: #6b7280; font-size: 1.05rem; margin-top: 0.5rem;">
                            Tu sesión caducó por inactividad o el token de seguridad expiró.
                        </p>
                    @endif

                    <div class="error-tips">
                        <ul style="margin: 0; padding: 0;">
                            <li>
                                <i class="fas fa-sync-alt"></i>
                                <strong>Actualizá la página</strong> para renovar el token de seguridad.
                            </li>
                            <li>
                                <i class="fas fa-sign-in-alt"></i>
                                <strong>Iniciá sesión nuevamente</strong> si el problema persiste.
                            </li>
                            <li>
                                <i class="fas fa-hourglass-end"></i>
                                Evitá dejar la pestaña abierta sin actividad por mucho tiempo.
                            </li>
                        </ul>
                    </div>

                    <div class="error-actions">
                        <a href="{{ route('login') }}" class="btn-ops-warning">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                        <a href="{{ route('home') }}" class="btn-ops-primary">
                            <i class="fas fa-home"></i> Ir al Inicio
                        </a>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="error-ops-panel__footer">
                <span style="color: #9ca3af; font-size: 0.85rem;">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Por seguridad, las sesiones expiran automáticamente después de un período de inactividad.
                </span>
            </div>
        </div>
    </div>
</div>
@endsection