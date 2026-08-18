<?php

use App\Models\Permission;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('rol can be created via factory', function () {
    $rol = Rol::factory()->create();

    expect($rol)->toBeInstanceOf(Rol::class);
    expect($rol->name)->not->toBeEmpty();
    expect($rol->seeded_permissions_locked)->toBeFalse();
});

test('rol has permisos relationship', function () {
    $rol = Rol::factory()->create();
    $perm1 = Permission::factory()->create();
    $perm2 = Permission::factory()->create();
    $rol->permisos()->attach([$perm1->id, $perm2->id]);

    expect($rol->permisos)->toHaveCount(2);
    expect($rol->permisos->pluck('id')->toArray())->toContain($perm1->id);
    expect($rol->permisos->pluck('id')->toArray())->toContain($perm2->id);
});

test('rol has users relationship', function () {
    $rol = Rol::factory()->create();
    $user1 = \App\Models\User::factory()->create();
    $user2 = \App\Models\User::factory()->create();
    $rol->users()->attach([$user1->id, $user2->id]);

    expect($rol->users)->toHaveCount(2);
    expect($rol->users->pluck('id')->toArray())->toContain($user1->id);
    expect($rol->users->pluck('id')->toArray())->toContain($user2->id);
});

test('rol table is rols not roles', function () {
    expect((new Rol)->getTable())->toBe('rols');
});

test('rol uses LogsActivity trait', function () {
    $rol = Rol::factory()->create();

    expect(method_exists($rol, 'getActivitylogOptions'))->toBeTrue();
});

test('rol permission seeded lock defaults to false', function () {
    $rol = Rol::factory()->create();

    expect($rol->seeded_permissions_locked)->toBeFalse();
});

test('rol with seeded lock can be created', function () {
    $rol = Rol::factory()->create(['seeded_permissions_locked' => true]);

    expect($rol->seeded_permissions_locked)->toBeTrue();
});
