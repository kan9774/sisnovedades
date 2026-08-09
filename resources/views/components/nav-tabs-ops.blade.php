@props([
    'tabs' => [],
    'active' => null,
    'wireMethod' => 'setTab',
    'livewire' => false,
])

<ul class="nav nav-tabs-ops mb-3" role="tablist">
    @foreach ($tabs as $key => $label)
        <li class="nav-item">

            @if ($livewire)
                <a class="nav-link-ops {{ $active === $key ? 'active' : '' }}" href="#"
                    wire:click.prevent="{{ $wireMethod }}('{{ $key }}')" role="tab">
                    {{ $label }}
                </a>
            @else
                <a class="nav-link-ops {{ $active === $key ? 'active' : '' }}" href="#{{ $key }}"
                    data-toggle="tab" role="tab">
                    {{ $label }}
                </a>
            @endif

        </li>
    @endforeach
</ul>
