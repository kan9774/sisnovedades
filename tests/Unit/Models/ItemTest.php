<?php

use App\Models\Categoria;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('item can be created via factory', function () {
    $item = Item::factory()->create();

    expect($item)->toBeInstanceOf(Item::class);
    expect($item->nombre)->not->toBeEmpty();
});

test('item nombre mutator uppercases and trims', function () {
    $item = Item::factory()->create(['nombre' => '  test item  ']);

    expect($item->nombre)->toBe('TEST ITEM');
});

test('item unidad_medida mutator uppercases and trims', function () {
    $item = Item::factory()->create(['unidad_medida' => '  kg  ']);

    expect($item->unidad_medida)->toBe('KG');
});

test('item esIndividual returns true for individual tracking', function () {
    $item = Item::factory()->create(['tipo_seguimiento' => 'individual']);

    expect($item->esIndividual())->toBeTrue();
    expect($item->esPorCantidad())->toBeFalse();
});

test('item esPorCantidad returns true for quantity tracking', function () {
    $item = Item::factory()->create(['tipo_seguimiento' => 'cantidad']);

    expect($item->esPorCantidad())->toBeTrue();
    expect($item->esIndividual())->toBeFalse();
});

test('item tieneVidaUtil returns true when vida_util_meses is set', function () {
    $item = Item::factory()->create(['vida_util_meses' => 12]);

    expect($item->tieneVidaUtil())->toBeTrue();
});

test('item tieneVidaUtil returns false when vida_util_meses is null', function () {
    $item = Item::factory()->create(['vida_util_meses' => null]);

    expect($item->tieneVidaUtil())->toBeFalse();
});

test('item categoria relationship', function () {
    $categoria = Categoria::factory()->create();
    $item = Item::factory()->create(['categoria_id' => $categoria->id]);

    expect($item->categoria)->toBeInstanceOf(Categoria::class);
    expect($item->categoria->id)->toBe($categoria->id);
});

test('item atributos is cast as array', function () {
    $item = Item::factory()->create(['atributos' => ['color' => 'rojo', 'talle' => 'L']]);

    expect($item->atributos)->toBeArray();
    expect($item->atributos['color'])->toBe('rojo');
    expect($item->atributos['talle'])->toBe('L');
});

test('item uses HasFactory trait', function () {
    $item = Item::factory()->make();

    expect($item)->toBeInstanceOf(Item::class);
});
