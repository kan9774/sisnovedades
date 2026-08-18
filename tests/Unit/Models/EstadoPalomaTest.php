<?php

use App\Models\EstadoPaloma;
use App\Models\Paloma;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('estado_paloma can be created via factory', function () {
    $estado = EstadoPaloma::factory()->create();

    expect($estado)->toBeInstanceOf(EstadoPaloma::class);
    expect($estado->nombre)->not->toBeEmpty();
    expect($estado->color)->not->toBeEmpty();
    expect($estado->activo)->toBeTrue();
});

test('estado_paloma table is estados_paloma', function () {
    expect((new EstadoPaloma)->getTable())->toBe('estados_paloma');
});

test('estado_paloma uses LogsActivity trait', function () {
    $estado = EstadoPaloma::factory()->create();

    expect(method_exists($estado, 'getActivitylogOptions'))->toBeTrue();
});

test('estado_paloma activo boolean is cast correctly', function () {
    $activo = EstadoPaloma::factory()->create(['activo' => true]);
    $inactivo = EstadoPaloma::factory()->create(['activo' => false]);

    expect($activo->activo)->toBeTrue();
    expect($inactivo->activo)->toBeFalse();
});

test('estado_paloma palomas relationship', function () {
    $estado = EstadoPaloma::factory()->create();
    $paloma1 = Paloma::factory()->create(['estado_id' => $estado->id]);
    $paloma2 = Paloma::factory()->create(['estado_id' => $estado->id]);

    expect($estado->palomas)->toHaveCount(2);
    expect($estado->palomas->pluck('id')->toArray())->toContain($paloma1->id);
    expect($estado->palomas->pluck('id')->toArray())->toContain($paloma2->id);
});
