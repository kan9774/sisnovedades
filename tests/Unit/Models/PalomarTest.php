<?php

use App\Models\Paloma;
use App\Models\Palomar;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('palomar can be created via factory', function () {
    $palomar = Palomar::factory()->create();

    expect($palomar)->toBeInstanceOf(Palomar::class);
    expect($palomar->nombre)->not->toBeEmpty();
    expect($palomar->capacidad_maxima)->toBeInt();
    expect($palomar->capacidad_maxima)->toBeGreaterThan(0);
});

test('palomar table is palomares', function () {
    expect((new Palomar)->getTable())->toBe('palomares');
});

test('palomar uses LogsActivity trait', function () {
    $palomar = Palomar::factory()->create();

    expect(method_exists($palomar, 'getActivitylogOptions'))->toBeTrue();
});

test('palomar palomas relationship', function () {
    $palomar = Palomar::factory()->create();
    $paloma1 = Paloma::factory()->create(['palomar_id' => $palomar->id]);
    $paloma2 = Paloma::factory()->create(['palomar_id' => $palomar->id]);

    expect($palomar->palomas)->toHaveCount(2);
    expect($palomar->palomas->pluck('id')->toArray())->toContain($paloma1->id);
    expect($palomar->palomas->pluck('id')->toArray())->toContain($paloma2->id);
});
