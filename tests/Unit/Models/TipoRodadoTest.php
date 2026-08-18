<?php

use App\Models\TipoRodado;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tipo_rodado can be created via factory', function () {
    $tipo = TipoRodado::factory()->create();

    expect($tipo)->toBeInstanceOf(TipoRodado::class);
    expect($tipo->nombre)->not->toBeEmpty();
});

test('tipo_rodado table is tipos_rodado', function () {
    expect((new TipoRodado)->getTable())->toBe('tipos_rodado');
});

test('tipo_rodado has vehiculos relationship', function () {
    $tipo = TipoRodado::factory()->create();
    $v1 = Vehiculo::factory()->create(['tipo_rodado_id' => $tipo->id]);
    $v2 = Vehiculo::factory()->create(['tipo_rodado_id' => $tipo->id]);

    expect($tipo->vehiculos)->toHaveCount(2);
});
