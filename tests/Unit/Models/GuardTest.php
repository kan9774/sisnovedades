<?php

use App\Models\Guard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->captain = User::factory()->create();
    $this->oficial = User::factory()->create();
});

test('guard can be created via factory', function () {
    $guard = Guard::factory()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard)->toBeInstanceOf(Guard::class);
    expect($guard->date)->not->toBeNull();
});

test('guard factory abierta creates open guard', function () {
    $guard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->status)->toBe('open');
    expect($guard->isAbierta())->toBeTrue();
});

test('guard factory cerrada creates closed guard', function () {
    $guard = Guard::factory()->cerrada()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->status)->toBe('closed');
    expect($guard->isCerrada())->toBeTrue();
});

test('scopeAbierta filters by open status', function () {
    Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    Guard::factory()->cerrada()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    $openGuards = Guard::abierta()->get();

    expect($openGuards)->toHaveCount(1);
    expect($openGuards->first()->status)->toBe('open');
});

test('scopeCerrada filters by closed status', function () {
    Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    Guard::factory()->cerrada()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    $closedGuards = Guard::cerrada()->get();

    expect($closedGuards)->toHaveCount(1);
    expect($closedGuards->first()->status)->toBe('closed');
});

test('scopeHoy filters by today date', function () {
    $todayGuard = Guard::factory()->create([
        'date' => today(),
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $otherGuard = Guard::factory()->create([
        'date' => now()->subDay(),
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    $todayGuards = Guard::hoy()->get();

    expect($todayGuards)->toHaveCount(1);
    expect($todayGuards->first()->id)->toBe($todayGuard->id);
});

test('scopeWithTrashed includes trashed guards', function () {
    $activeGuard = Guard::factory()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $trashedGuard = Guard::factory()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $trashedGuard->delete();

    $allGuards = Guard::withTrashed()->get();

    expect($allGuards)->toHaveCount(2);
    expect($allGuards->pluck('id')->toArray())->toContain($activeGuard->id);
    expect($allGuards->pluck('id')->toArray())->toContain($trashedGuard->id);
});

test('scopeOnlyTrashed returns only trashed guards', function () {
    $activeGuard = Guard::factory()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $trashedGuard = Guard::factory()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $trashedGuard->delete();

    $onlyTrashed = Guard::onlyTrashed()->get();

    expect($onlyTrashed)->toHaveCount(1);
    expect($onlyTrashed->first()->id)->toBe($trashedGuard->id);
});

test('isAbierta returns true for open guard', function () {
    $guard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->isAbierta())->toBeTrue();
});

test('isAbierta returns false for closed guard', function () {
    $guard = Guard::factory()->cerrada()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->isAbierta())->toBeFalse();
});

test('isCerrada returns true for closed non-deleted guard', function () {
    $guard = Guard::factory()->cerrada()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->isCerrada())->toBeTrue();
});

test('isCerrada returns false for deleted guard', function () {
    $guard = Guard::factory()->cerrada()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $guard->delete();

    expect($guard->isCerrada())->toBeFalse();
});

test('isEliminada returns true for deleted guard', function () {
    $guard = Guard::factory()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->isEliminada())->toBeFalse();

    $guard->delete();

    expect($guard->isEliminada())->toBeTrue();
});

test('isAbiertaNoDelete returns true only for active open guards', function () {
    $activeGuard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $deletedOpenGuard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $deletedOpenGuard->delete();

    expect($activeGuard->isAbiertaNoDelete())->toBeTrue();
    expect($deletedOpenGuard->isAbiertaNoDelete())->toBeFalse();
});

test('esMiembro returns true for captain', function () {
    $guard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->esMiembro($this->captain))->toBeTrue();
});

test('esMiembro returns true for oficial', function () {
    $guard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->esMiembro($this->oficial))->toBeTrue();
});

test('esMiembro returns false for unrelated user', function () {
    $unrelatedUser = User::factory()->create();
    $guard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->esMiembro($unrelatedUser))->toBeFalse();
});

test('esMiembro returns false for null user', function () {
    $guard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->esMiembro(null))->toBeFalse();
});

test('esMiembro uses eager-loaded escribiente relation', function () {
    $escribiente = User::factory()->create();
    $guard = Guard::factory()->abierta()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);
    $guard->escribiente()->attach($escribiente->id, [
        'hora_inicio' => '08:00:00',
        'hora_fin' => '20:00:00',
    ]);
    $guard->load('escribiente');

    expect($guard->esMiembro($escribiente))->toBeTrue();
});

test('guard table is guards', function () {
    expect((new Guard)->getTable())->toBe('guards');
});

test('guard uses SoftDeletes trait', function () {
    $guard = Guard::factory()->create([
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    $guard->delete();
    expect(Guard::find($guard->id))->toBeNull();
    expect(Guard::withTrashed()->find($guard->id))->not->toBeNull();
});

test('guard date is cast to date', function () {
    $date = now();
    $guard = Guard::factory()->create([
        'date' => $date,
        'captain_id' => $this->captain->id,
        'oficer_id' => $this->oficial->id,
    ]);

    expect($guard->date)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    expect($guard->date->format('Y-m-d'))->toBe($date->format('Y-m-d'));
});
