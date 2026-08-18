<?php

use App\Models\TipoCombustible;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tipo_combustible can be created via factory', function () {
    $tipo = TipoCombustible::factory()->create();

    expect($tipo)->toBeInstanceOf(TipoCombustible::class);
    expect($tipo->nombre)->not->toBeEmpty();
});

test('tipo_combustible table is tipos_combustible', function () {
    expect((new TipoCombustible)->getTable())->toBe('tipos_combustible');
});

test('tipo_combustible has vehiculos relationship', function () {
    $tipo = TipoCombustible::factory()->create();
    $v1 = Vehiculo::factory()->create(['tipo_combustible_id' => $tipo->id]);
    $v2 = Vehiculo::factory()->create(['tipo_combustible_id' => $tipo->id]);

    expect($tipo->vehiculos)->toHaveCount(2);
});
