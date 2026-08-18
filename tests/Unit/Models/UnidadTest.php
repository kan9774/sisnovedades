<?php

use App\Models\Unidad;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unidad can be created via factory', function () {
    $unidad = Unidad::factory()->create();

    expect($unidad)->toBeInstanceOf(Unidad::class);
    expect($unidad->nombre)->not->toBeEmpty();
});

test('unidad table is unidades', function () {
    expect((new Unidad)->getTable())->toBe('unidades');
});

test('unidad uses LogsActivity trait', function () {
    $unidad = Unidad::factory()->create();

    expect(method_exists($unidad, 'getActivitylogOptions'))->toBeTrue();
});

test('unidad vehiculos relationship', function () {
    $unidad = Unidad::factory()->create();
    $v1 = Vehiculo::factory()->create(['unidad_id' => $unidad->id]);
    $v2 = Vehiculo::factory()->create(['unidad_id' => $unidad->id]);

    expect($unidad->vehiculos)->toHaveCount(2);
    expect($unidad->vehiculos->pluck('id')->toArray())->toContain($v1->id);
    expect($unidad->vehiculos->pluck('id')->toArray())->toContain($v2->id);
});

test('unidad usuarios relationship', function () {
    $unidad = Unidad::factory()->create();
    $user1 = User::factory()->create(['unidad_id' => $unidad->id]);
    $user2 = User::factory()->create(['unidad_id' => $unidad->id]);

    expect($unidad->usuarios)->toHaveCount(2);
    expect($unidad->usuarios->pluck('id')->toArray())->toContain($user1->id);
    expect($unidad->usuarios->pluck('id')->toArray())->toContain($user2->id);
});
