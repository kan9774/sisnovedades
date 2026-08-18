<?php

use App\Models\Organismo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('organismo can be created via factory', function () {
    $organismo = Organismo::factory()->create();

    expect($organismo)->toBeInstanceOf(Organismo::class);
    expect($organismo->name)->not->toBeEmpty();
});

test('organismo table is organismos', function () {
    expect((new Organismo)->getTable())->toBe('organismos');
});
