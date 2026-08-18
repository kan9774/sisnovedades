<?php

use App\Models\Ubicacion;
use App\Models\Item;
use App\Models\Movimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ubicacion can be created via factory', function () {
    $ubicacion = Ubicacion::factory()->create();

    expect($ubicacion)->toBeInstanceOf(Ubicacion::class);
    expect($ubicacion->nombre)->not->toBeEmpty();
    expect($ubicacion->tipo)->not->toBeEmpty();
});

test('ubicacion table is ubicaciones', function () {
    expect((new Ubicacion)->getTable())->toBe('ubicaciones');
});
