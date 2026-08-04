@props(['title' => null, 'titleSuffix' => null, 'eyebrow' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'card card-outline-ops']) }}>
    @if($title || $eyebrow)
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                @if($title)
                    <h3 class="card-title-ops mb-0">
                        @if($icon)
                            <i class="fas fa-{{ $icon }}"></i>
                        @endif
                        {{ $title }}
                        @if($titleSuffix)
                            <span class="card-title-ops__suffix">{{ $titleSuffix }}</span>
                        @endif
                    </h3>
                @endif
                @if($eyebrow)
                    <span class="card-header-ops__eyebrow">{{ $eyebrow }}</span>
                @endif
            </div>
        </div>
    @endif

    @isset($header)
        <div class="card-subheader-ops">
            {{ $header }}
        </div>
    @endisset

    <div class="card-body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="card-footer bg-white border-0 pt-4 pb-4">
            {{ $footer }}
        </div>
    @endisset
</div>