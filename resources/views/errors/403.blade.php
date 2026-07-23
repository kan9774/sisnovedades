@extends('layouts.app')

@section('title', '403 - No autorizado')
@section('subtitle', 'Error 403')



@section('content_body')
<div class="error-ops-panel-overlay">
    <div class="error-ops-panel">
        <div class="error-ops-panel__form">
            {{-- HEADER --}}
            <header class="error-ops-panel__header">
                <div>
                    <span class="error-ops-panel__eyebrow">Error de acceso</span>
                    <h1 class="error-ops-panel__title">Acceso Denegado</h1>
                </div>
                <a href="{{ route('admin.index') }}" class="error-ops-panel__close" title="Volver al inicio">
                    <i class="fas fa-times"></i>
                </a>
            </header>

            {{-- BODY --}}
            <div class="error-ops-panel__body">
                <div class="error-ops-panel__content">
                    <div class="error-icon-wrapper">
                        <i class="fas fa-lock error-lock"></i>
                        <span class="error-code">403</span>
                    </div>

                    <h2 style="color: #0B2545; font-weight: 600; margin-bottom: 0.5rem;">
                        {{ $exception->getMessage() ? 'Acceso Restringido' : '¡No tenés permisos!' }}
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
                            No podés acceder a esta página. Necesitás permisos adecuados o iniciar sesión.
                        </p>
                    @endif

                    <div class="error-actions">
                        <a href="{{ route('admin.index') }}" class="btn btn-ops-primary">
                            <i class="fas fa-home mr-1"></i> Volver al Inicio
                        </a>
                        @auth
                        <a href="javascript:history.back()" class="btn btn-ops-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Volver atrás
                        </a>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="error-ops-panel__footer">
                <span style="color: #9ca3af; font-size: 0.85rem;">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Si creés que esto es un error, contactá al administrador.
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
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

    .error-icon-wrapper .error-lock {
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
    }

    .btn-ops-primary:hover {
        background: linear-gradient(135deg, #0F3460 0%, #1a4a7a 100%);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(11, 37, 69, 0.25);
    }

    .btn-ops-secondary {
        background: #f1f3f5;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 0.6rem 1.8rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all .15s;
    }

    .btn-ops-secondary:hover {
        background: #e5e7eb;
        border-color: #9ca3af;
        color: #111827;
    }

    .error-ops-panel__close:hover {
        background: rgba(255, 210, 0, 0.18);
        border-color: #FFD200;
        color: #FFD200;
        text-decoration: none;
    }

    @keyframes opsPanelFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    body.ops-panel-open {
        overflow: hidden;
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
        .error-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush