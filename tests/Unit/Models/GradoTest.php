<?php

use App\Models\Grado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('grado can be created via factory', function () {
    $grado = Grado::factory()->create();

    expect($grado)->toBeInstanceOf(Grado::class);
    expect($grado->nombre)->not->toBeEmpty();
});

test('grado users relationship', function () {
    $grado = Grado::factory()->create();
    $user1 = User::factory()->create(['grado_id' => $grado->id]);
    $user2 = User::factory()->create(['grado_id' => $grado->id]);

    expect($grado->usuarios)->toHaveCount(2);
    expect($grado->usuarios->pluck('id')->toArray())->toContain($user1->id);
    expect($grado->usuarios->pluck('id')->toArray())->toContain($user2->id);
});
