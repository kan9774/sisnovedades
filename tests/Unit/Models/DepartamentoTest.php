<?php

use App\Models\Departamento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('departamento can be created via factory', function () {
    $depto = Departamento::factory()->create();

    expect($depto)->toBeInstanceOf(Departamento::class);
    expect($depto->nombre)->not->toBeEmpty();
    expect($depto->codigo_ine)->not->toBeEmpty();
});

test('departamento table is departamentos', function () {
    expect((new Departamento)->getTable())->toBe('departamentos');
});

test('departamento codigo_ine is numeric', function () {
    $depto = Departamento::factory()->create();

    expect($depto->codigo_ine)->toMatch('/^\d{2}$/');
});
