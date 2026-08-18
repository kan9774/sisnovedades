<?php

use App\Models\Oficina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('oficina can be created via factory', function () {
    $oficina = Oficina::factory()->create();

    expect($oficina)->toBeInstanceOf(Oficina::class);
    expect($oficina->nombre)->not->toBeEmpty();
});

test('oficina table is oficinas', function () {
    expect((new Oficina)->getTable())->toBe('oficinas');
});

test('oficina uses LogsActivity trait', function () {
    $oficina = Oficina::factory()->create();

    expect(method_exists($oficina, 'getActivitylogOptions'))->toBeTrue();
});

test('oficina users relationship', function () {
    $oficina = Oficina::factory()->create();
    $user1 = User::factory()->create(['oficina_id' => $oficina->id]);
    $user2 = User::factory()->create(['oficina_id' => $oficina->id]);

    expect($oficina->users)->toHaveCount(2);
    expect($oficina->users->pluck('id')->toArray())->toContain($user1->id);
    expect($oficina->users->pluck('id')->toArray())->toContain($user2->id);
});
