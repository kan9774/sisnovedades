@props(['variant' => 'primary', 'icon' => null, 'href' => null])

@php
    $classes = "btn-ops btn-ops-{$variant}";
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if($icon)
        <i class="fas fa-{{ $icon }}"></i>
    @endif
    {{ $slot }}
</{{ $tag }}>