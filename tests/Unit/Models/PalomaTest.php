<?php

use App\Models\EstadoPaloma;
use App\Models\Paloma;
use App\Models\Palomar;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('paloma can be created via factory', function () {
    $paloma = Paloma::factory()->create();

    expect($paloma)->toBeInstanceOf(Paloma::class);
    expect($paloma->anilla)->not->toBeEmpty();
});

test('paloma factory pichon creates young pigeon', function () {
    $paloma = Paloma::factory()->pichon()->create();

    expect($paloma->esPichon)->toBeTrue();
});

test('paloma factory adulta creates adult pigeon', function () {
    $paloma = Paloma::factory()->adulta()->create();

    expect($paloma->esPichon)->toBeFalse();
});

test('esPichon returns true when born less than 6 months ago', function () {
    $paloma = Paloma::factory()->pichon()->create();

    expect($paloma->esPichon)->toBeTrue();
});

test('esPichon returns false when born more than 6 months ago', function () {
    $paloma = Paloma::factory()->adulta()->create();

    expect($paloma->esPichon)->toBeFalse();
});

test('scopeActivas returns only active pigeons', function () {
    // We need to create an active state first
    $estadoActivo = EstadoPaloma::firstOrCreate(['nombre' => 'Activa'], ['color' => 'green', 'activo' => true]);
    $estadoInactivo = EstadoPaloma::firstOrCreate(['nombre' => 'Inactiva'], ['color' => 'gray', 'activo' => false]);

    $palomaActiva = Paloma::factory()->create(['estado_id' => $estadoActivo->id]);
    $palomaInactiva = Paloma::factory()->create(['estado_id' => $estadoInactivo->id]);

    $activas = Paloma::activas()->get();

    expect($activas)->toHaveCount(1);
    expect($activas->first()->id)->toBe($palomaActiva->id);
});

test('scopePichones returns young pigeons', function () {
    Paloma::factory()->pichon()->create();
    Paloma::factory()->adulta()->create();

    $pichones = Paloma::pichones()->get();

    expect($pichones)->toHaveCount(1);
});

test('scopeAdultos returns adult pigeons', function () {
    Paloma::factory()->pichon()->create();
    Paloma::factory()->adulta()->create();

    $adultos = Paloma::adultos()->get();

    expect($adultos)->toHaveCount(1);
});

test('paloma palomar relationship', function () {
    $palomar = Palomar::factory()->create();
    $paloma = Paloma::factory()->create(['palomar_id' => $palomar->id]);

    expect($paloma->palomar)->toBeInstanceOf(Palomar::class);
    expect($paloma->palomar->id)->toBe($palomar->id);
});

test('paloma estado relationship', function () {
    $estado = EstadoPaloma::factory()->create();
    $paloma = Paloma::factory()->create(['estado_id' => $estado->id]);

    expect($paloma->estado)->toBeInstanceOf(EstadoPaloma::class);
    expect($paloma->estado->id)->toBe($estado->id);
});

test('paloma historial relationship', function () {
    $paloma = Paloma::factory()->create();
    $historial = \App\Models\HistorialPaloma::create([
        'paloma_id' => $paloma->id,
        'user_id' => 1,
        'evento' => 'cambio_estado',
        'fecha_evento' => now(),
        'observaciones' => 'test',
    ]);

    expect($paloma->historial)->toHaveCount(1);
    expect($paloma->historial->first()->id)->toBe($historial->id);
});

test('paloma uses LogsActivity trait', function () {
    $paloma = Paloma::factory()->create();

    expect(method_exists($paloma, 'getActivitylogOptions'))->toBeTrue();
});
