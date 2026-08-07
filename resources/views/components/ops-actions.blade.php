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

    // Nuevos: texto configurable del SweetAlert2 de borrado, con default genérico
    'deleteTitle' => '¿Eliminar registro?',
    'deleteText' => 'Esta acción no se puede deshacer.',
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
            x-on:click="
                confirmarAccion({
                    title: @js($deleteTitle),
                    text: @js($deleteText),
                    confirmButtonText: 'Sí, eliminar',
                    onConfirm: () => $wire.{{ $delete }},
                })
            "
            wire:loading.attr="disabled"
            wire:target="{{ $delete }}"
            class="btn-ops btn-ops-danger btn-ops-icon {{ $sizeClass }}"
            title="Eliminar"
        >
            <span wire:loading.remove wire:target="{{ $delete }}">
                <i class="fas fa-trash"></i>
            </span>
            <span wire:loading wire:target="{{ $delete }}">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
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