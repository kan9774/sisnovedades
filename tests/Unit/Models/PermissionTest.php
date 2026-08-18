<?php

use App\Models\Permission;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('permission can be created via factory', function () {
    $permission = Permission::factory()->create();

    expect($permission)->toBeInstanceOf(Permission::class);
    expect($permission->name)->not->toBeEmpty();
    expect($permission->model)->not->toBeEmpty();
});

test('permission has rols relationship', function () {
    $permission = Permission::factory()->create();
    $rol1 = Rol::factory()->create();
    $rol2 = Rol::factory()->create();
    $permission->rols()->attach([$rol1->id, $rol2->id]);

    expect($permission->rols)->toHaveCount(2);
    expect($permission->rols->pluck('id')->toArray())->toContain($rol1->id);
    expect($permission->rols->pluck('id')->toArray())->toContain($rol2->id);
});

test('permission uses LogsActivity trait', function () {
    $permission = Permission::factory()->create();

    expect(method_exists($permission, 'getActivitylogOptions'))->toBeTrue();
});
