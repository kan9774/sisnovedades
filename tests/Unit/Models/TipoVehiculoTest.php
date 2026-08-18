<?php

use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tipo_vehiculo can be created via factory', function () {
    $tipo = TipoVehiculo::factory()->create();

    expect($tipo)->toBeInstanceOf(TipoVehiculo::class);
    expect($tipo->nombre)->not->toBeEmpty();
    expect($tipo->activo)->toBeTrue();
});

test('tipo_vehiculo table is tipos_vehiculo', function () {
    expect((new TipoVehiculo)->getTable())->toBe('tipos_vehiculo');
});

test('tipo_vehiculo uses LogsActivity trait', function () {
    $tipo = TipoVehiculo::factory()->create();

    expect(method_exists($tipo, 'getActivitylogOptions'))->toBeTrue();
});

test('tipo_vehiculo activo boolean is cast correctly', function () {
    $activo = TipoVehiculo::factory()->create(['activo' => true]);
    $inactivo = TipoVehiculo::factory()->create(['activo' => false]);

    expect($activo->activo)->toBeTrue();
    expect($inactivo->activo)->toBeFalse();
});

test('tipo_vehiculo vehiculos relationship', function () {
    $tipo = TipoVehiculo::factory()->create();
    $v1 = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);
    $v2 = Vehiculo::factory()->create(['tipo_vehiculo_id' => $tipo->id]);

    expect($tipo->vehiculos)->toHaveCount(2);
    expect($tipo->vehiculos->pluck('id')->toArray())->toContain($v1->id);
    expect($tipo->vehiculos->pluck('id')->toArray())->toContain($v2->id);
});
