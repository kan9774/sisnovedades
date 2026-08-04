@props([
    'tabs' => [],
    'active' => null,
    'wireMethod' => 'setTab',
])

<ul class="nav nav-tabs-ops mb-3" role="tablist">
    @foreach ($tabs as $key => $label)
        <li class="nav-item">
            <a class="nav-link-ops {{ $active === $key ? 'active' : '' }}" href="#"
                wire:click.prevent="{{ $wireMethod }}('{{ $key }}')">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>