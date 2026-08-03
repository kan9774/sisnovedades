@props([
    'view' => null,
    'edit' => null,
    'delete' => null,
    'restore' => null,
    'model' => null,
    'canView' => 'view',
    'canEdit' => 'update',
    'canDelete' => 'delete',
    'canRestore' => 'restore',
    'showEdit' => true,
    'showDelete' => true,
    'showRestore' => true,
    'size' => null, // null (default 34px) | 'xs' | 'sm'
])

@php
    $sizeClass = $size ? "btn-ops-icon--{$size}" : '';
@endphp

<div {{ $attributes->merge(['class' => 'ops-actions']) }}>
    @if($view && (!$model || auth()->user()->can($canView, $model)))
        <a href="{{ $view }}" class="btn-ops btn-ops-info btn-ops-icon {{ $sizeClass }}" title="Ver">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if($edit && $showEdit && (!$model || auth()->user()->can($canEdit, $model)))
        <button
            type="button"
            wire:click="{{ $edit }}"
            class="btn-ops btn-ops-warning btn-ops-icon {{ $sizeClass }}"
            title="Editar"
        >
            <i class="fas fa-pen"></i>
        </button>
    @endif

    @if($delete && $showDelete && (!$model || auth()->user()->can($canDelete, $model)))
        <button
            type="button"
            wire:click="{{ $delete }}"
            wire:confirm="¿Confirmás que querés eliminar este registro?"
            wire:loading.attr="disabled"
            wire:target="{{ $delete }}"
            class="btn-ops btn-ops-danger btn-ops-icon {{ $sizeClass }}"
            title="Eliminar"
        >
            <i class="fas fa-trash"></i>
        </button>
    @endif

    @if($restore && $showRestore && (!$model || auth()->user()->can($canRestore, $model)))
        <button
            type="button"
            wire:click="{{ $restore }}"
            class="btn-ops btn-ops-success btn-ops-icon {{ $sizeClass }}"
            title="Restaurar"
        >
            <i class="fas fa-rotate-left"></i>
        </button>
    @endif

    {{ $slot ?? '' }}
</div>