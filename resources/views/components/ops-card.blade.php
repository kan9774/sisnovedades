@props(['title' => null, 'eyebrow' => null])

<div {{ $attributes->merge(['class' => 'card card-outline-ops']) }}>
    @if($title || $eyebrow || isset($header))
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                @if($title)
                    <h3 class="card-title-ops mb-0">{{ $title }}</h3>
                @endif
                @if($eyebrow)
                    <span class="card-header-ops__eyebrow">{{ $eyebrow }}</span>
                @endif
            </div>
            {{ $header ?? '' }}
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</div>