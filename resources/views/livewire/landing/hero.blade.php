<section id="inicio" class="hero-section channel-view" x-show="seccion === 'inicio'"
    x-transition.opacity.duration.400ms>
    <div class="hero-grid-overlay"></div>
    <div class="hero-scanline"></div>

    <div class="container text-center hero-content">
        <span class="hero-eyebrow">// BCOM1 · TRANSMISIÓN ACTIVA</span>

        <h1 class="hero-title">Comunicaciones</h1>
        <div class="hero-subtitle2">Ejército Nacional</div>

        <svg class="hero-waveform" viewBox="0 0 640 90" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0,45 L60,45 L80,15 L100,75 L120,25 L140,65 L160,45 L220,45
                     L240,10 L260,80 L280,45 L340,45 L360,20 L380,70 L400,45
                     L460,45 L480,15 L500,75 L520,45 L640,45" />
        </svg>

        <p class="hero-subtitle mx-auto">
            Conectando al país, garantizando la seguridad y soberanía nacional a través de
            redes tácticas y estratégicas de comunicación.
        </p>

        <div class="hero-ctas">
            <a href="#" @click.prevent="seccion = 'servicios'" class="btn btn-primary btn-lg mx-2">
                <i class="fas fa-satellite-dish mr-2"></i> Ver servicios
            </a>
            <a href="#" @click.prevent="seccion = 'contacto'" class="btn btn-outline-light btn-lg mx-2">
                <i class="fas fa-envelope mr-2"></i> Contacto
            </a>
        </div>

        <div class="hero-readout">
            <span><strong>LAT</strong> -34.825610</span>
            <span><strong>LON</strong> -56.197976</span>
            <span><strong>UNIDAD</strong> Cuartel Peñarol</span>
            <span><strong>CANAL</strong> QAP</span>
        </div>
    </div>
</section>