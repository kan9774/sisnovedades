<?php

use App\Models\TipoLubricante;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tipo_lubricante can be created via factory', function () {
    $tipo = TipoLubricante::factory()->create();

    expect($tipo)->toBeInstanceOf(TipoLubricante::class);
    expect($tipo->nombre)->not->toBeEmpty();
});

test('tipo_lubricante table is tipos_lubricante', function () {
    expect((new TipoLubricante)->getTable())->toBe('tipos_lubricante');
});

test('tipo_lubricante has vehiculo relationship', function () {
    $tipo = TipoLubricante::factory()->create();
    $v1 = Vehiculo::factory()->create(['tipo_lubricante_id' => $tipo->id]);
    $v2 = Vehiculo::factory()->create(['tipo_lubricante_id' => $tipo->id]);

    expect($tipo->vehiculo)->toHaveCount(2);
});
