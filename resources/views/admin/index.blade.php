@extends('layouts.app')

@section('subtitle', 'Panel Principal')
@section('content_header_title', 'Inicio')
@section('content_header_subtitle', 'Dashboard de Operaciones')

@section('content_body')
<div class="container-fluid">
    {{-- Tarjeta principal "Próximamente" adaptada a AdminLTE --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg" style="border-radius: 24px; overflow: hidden; border: none;">
                <div class="card-body" style="padding: 2.5rem 2rem; background: linear-gradient(145deg, #f8faff 0%, #e9effa 100%);">
                    
                    {{-- Contenido centrado --}}
                    <div class="text-center" style="max-width: 720px; margin: 0 auto;">

                        {{-- Insignia "muy pronto" --}}
                        <div class="badge bg-primary bg-gradient px-4 py-2 rounded-pill mb-3" style="font-size: 0.75rem; letter-spacing: 0.08em; background: rgba(42, 92, 255, 0.12) !important; color: #1a4cff !important; border: 1px solid rgba(42, 92, 255, 0.15);">
                            <i class="fas fa-clock me-2"></i> muy pronto
                        </div>

                        {{-- Ícono principal --}}
                        <div class="d-inline-flex align-items-center justify-content-center p-4 rounded-circle mb-4" style="background: rgba(60, 100, 255, 0.08); border: 1px solid rgba(60, 100, 255, 0.15); backdrop-filter: blur(4px);">
                            <i class="fas fa-rocket text-primary" style="font-size: 3.8rem; filter: drop-shadow(0 6px 12px rgba(42, 92, 255, 0.15));"></i>
                        </div>

                        {{-- Título --}}
                        <h1 class="display-4 fw-bold mb-2" style="color: #0b1b3a; letter-spacing: -0.02em;">
                            <span style="background: linear-gradient(135deg, #1a4cff, #7a4aff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Próximamente</span>
                        </h1>

                        {{-- Subtítulo --}}
                        <div class="d-inline-block px-4 py-2 rounded-pill mb-4" style="background: rgba(255,255,255,0.5); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.6);">
                            <i class="fas fa-arrow-right me-2" style="opacity: 0.5;"></i> contenido en desarrollo
                        </div>

                        {{-- Cuenta regresiva --}}
                        <div class="d-flex justify-content-center gap-3 gap-md-4 my-4 flex-wrap">
                            <div class="px-3 py-2 rounded-pill text-center" style="min-width: 80px; background: rgba(255,255,255,0.6); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 6px 14px rgba(0,0,0,0.02);">
                                <span class="d-block fw-bold h3 mb-0" id="dias" style="color: #0b1b3a;">18</span>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.06em; font-weight: 600;">días</small>
                            </div>
                            <div class="px-3 py-2 rounded-pill text-center" style="min-width: 80px; background: rgba(255,255,255,0.6); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 6px 14px rgba(0,0,0,0.02);">
                                <span class="d-block fw-bold h3 mb-0" id="horas" style="color: #0b1b3a;">06</span>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.06em; font-weight: 600;">horas</small>
                            </div>
                            <div class="px-3 py-2 rounded-pill text-center" style="min-width: 80px; background: rgba(255,255,255,0.6); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 6px 14px rgba(0,0,0,0.02);">
                                <span class="d-block fw-bold h3 mb-0" id="minutos" style="color: #0b1b3a;">42</span>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.06em; font-weight: 600;">min</small>
                            </div>
                            <div class="px-3 py-2 rounded-pill text-center" style="min-width: 80px; background: rgba(255,255,255,0.6); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 6px 14px rgba(0,0,0,0.02);">
                                <span class="d-block fw-bold h3 mb-0" id="segundos" style="color: #0b1b3a;">30</span>
                                <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.06em; font-weight: 600;">seg</small>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <p class="d-inline-block px-4 py-2 rounded-pill mb-4" style="background: rgba(255,255,255,0.3); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.3); color: #1f3b62; font-size: 1.05rem;">
                            <i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle; margin-right: 8px; color: #2a5cff;"></i>
                            Estamos preparando algo increíble para ti.
                            <i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle; margin-left: 8px; color: #2a5cff;"></i>
                        </p>

                        {{-- Botón de notificación --}}
                        <button class="btn btn-primary btn-lg rounded-pill px-5 py-2" id="notifyBtn" style="background: #0b1b3a; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 12px 24px -10px rgba(11, 27, 58, 0.25); font-weight: 600;">
                            <i class="fas fa-bell me-2"></i> Notificarme
                        </button>

                        {{-- Redes sociales --}}
                        <div class="mt-4 pt-3 d-flex justify-content-center gap-3 flex-wrap" style="border-top: 1px solid rgba(60, 100, 255, 0.10);">
                            <a href="#" class="d-inline-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width: 44px; height: 44px; color: #2d4b78; background: rgba(255,255,255,0.3); backdrop-filter: blur(2px); border: 1px solid rgba(255,255,255,0.3); transition: all 0.2s;" onmouseover="this.style.background='white'; this.style.color='#1a4cff'; this.style.transform='translateY(-4px)';" onmouseout="this.style.background='rgba(255,255,255,0.3)'; this.style.color='#2d4b78'; this.style.transform='translateY(0)';">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="d-inline-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width: 44px; height: 44px; color: #2d4b78; background: rgba(255,255,255,0.3); backdrop-filter: blur(2px); border: 1px solid rgba(255,255,255,0.3); transition: all 0.2s;" onmouseover="this.style.background='white'; this.style.color='#1a4cff'; this.style.transform='translateY(-4px)';" onmouseout="this.style.background='rgba(255,255,255,0.3)'; this.style.color='#2d4b78'; this.style.transform='translateY(0)';">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="d-inline-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width: 44px; height: 44px; color: #2d4b78; background: rgba(255,255,255,0.3); backdrop-filter: blur(2px); border: 1px solid rgba(255,255,255,0.3); transition: all 0.2s;" onmouseover="this.style.background='white'; this.style.color='#1a4cff'; this.style.transform='translateY(-4px)';" onmouseout="this.style.background='rgba(255,255,255,0.3)'; this.style.color='#2d4b78'; this.style.transform='translateY(0)';">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="#" class="d-inline-flex align-items-center justify-content-center rounded-circle text-decoration-none" style="width: 44px; height: 44px; color: #2d4b78; background: rgba(255,255,255,0.3); backdrop-filter: blur(2px); border: 1px solid rgba(255,255,255,0.3); transition: all 0.2s;" onmouseover="this.style.background='white'; this.style.color='#1a4cff'; this.style.transform='translateY(-4px)';" onmouseout="this.style.background='rgba(255,255,255,0.3)'; this.style.color='#2d4b78'; this.style.transform='translateY(0)';">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        </div>

                        {{-- Pie de página sutil --}}
                        <div class="mt-3 pt-2" style="font-size: 0.7rem; color: #9bb0d0; border-top: 1px solid rgba(0,0,0,0.02);">
                            <i class="fas fa-code me-1"></i> · 2026 · <i class="fas fa-circle" style="font-size: 0.3rem; margin: 0 6px; vertical-align: middle;"></i> estamos trabajando
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@stop

{{-- Script para cuenta regresiva e interacción del botón --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ---- Cuenta regresiva ----
        // Fecha objetivo: 18 de agosto de 2026, 20:30:00 (puedes modificarla)
        const targetDate = new Date('2026-08-18T20:30:00').getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            let diff = targetDate - now;
            if (diff < 0) diff = 0;

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('dias').textContent = String(days).padStart(2, '0');
            document.getElementById('horas').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutos').textContent = String(minutes).padStart(2, '0');
            document.getElementById('segundos').textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);

        // ---- Botón "Notificarme" ----
        const btn = document.getElementById('notifyBtn');
        btn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check-circle me-2"></i> ¡Listo!';
            this.style.background = '#1a7a3a';
            this.style.boxShadow = '0 8px 20px -6px rgba(26, 122, 58, 0.3)';
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.background = '#0b1b3a';
                this.style.boxShadow = '0 12px 24px -10px rgba(11, 27, 58, 0.25)';
            }, 2400);
        });
    });
</script>
@endpush