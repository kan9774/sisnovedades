<?php

use App\Models\Permission;
use App\Models\Rol;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->adminRol = Rol::firstOrCreate(['name' => 'admin']);
    $this->admin->roles()->attach($this->adminRol);
    $this->admin->load('roles');

    $this->superAdmin = User::factory()->create(['is_super_admin' => true]);
    $this->regularUser = User::factory()->create();
});

test('viewAny is allowed for all users', function () {
    expect($this->regularUser->can('viewAny', User::class))->toBeTrue();
    expect($this->admin->can('viewAny', User::class))->toBeTrue();
    expect($this->superAdmin->can('viewAny', User::class))->toBeTrue();
});

test('create requires crear_usuario permission', function () {
    expect($this->regularUser->can('create', User::class))->toBeFalse();

    $permission = Permission::create(['name' => 'crear_usuario']);
    $this->regularUser->permisosDirectos()->attach($permission);
    $this->regularUser->load('permisosDirectos');

    expect($this->regularUser->can('create', User::class))->toBeTrue();
});

test('create allows super admin', function () {
    expect($this->superAdmin->can('create', User::class))->toBeTrue();
});

test('create allows admin role with permission', function () {
    expect($this->admin->can('create', User::class))->toBeFalse();

    $permission = Permission::create(['name' => 'crear_usuario']);
    $this->adminRol->permisos()->attach($permission);
    $this->admin->load('roles');

    expect($this->admin->can('create', User::class))->toBeTrue();
});

test('update allows super admin to edit any user', function () {
    expect($this->superAdmin->can('update', $this->regularUser))->toBeTrue();
    expect($this->superAdmin->can('update', $this->admin))->toBeTrue();
    expect($this->superAdmin->can('update', $this->superAdmin))->toBeTrue();
});

test('update prevents admin from editing super admin', function () {
    expect($this->admin->can('update', $this->superAdmin))->toBeFalse();
});

test('update allows admin to edit regular user', function () {
    expect($this->admin->can('update', $this->regularUser))->toBeTrue();
});

test('update requires editar_usuario permission for regular user', function () {
    expect($this->regularUser->can('update', $this->regularUser))->toBeFalse();

    $permission = Permission::create(['name' => 'editar_usuario']);
    $this->regularUser->permisosDirectos()->attach($permission);
    $this->regularUser->load('permisosDirectos');

    expect($this->regularUser->can('update', $this->regularUser))->toBeTrue();
});

test('delete allows super admin to delete any user', function () {
    expect($this->superAdmin->can('delete', $this->regularUser))->toBeTrue();
    expect($this->superAdmin->can('delete', $this->admin))->toBeTrue();
    expect($this->superAdmin->can('delete', $this->superAdmin))->toBeTrue();
});

test('delete prevents admin from deleting super admin', function () {
    expect($this->admin->can('delete', $this->superAdmin))->toBeFalse();
});

test('delete allows admin to delete regular user', function () {
    expect($this->admin->can('delete', $this->regularUser))->toBeTrue();
});

test('delete denies regular user', function () {
    expect($this->regularUser->can('delete', $this->regularUser))->toBeFalse();
});

test('assignPermissions requires admin role', function () {
    expect($this->regularUser->can('assignPermissions', $this->regularUser))->toBeFalse();
    expect($this->admin->can('assignPermissions', $this->regularUser))->toBeTrue();
});

test('assignPermissions allows super admin', function () {
    expect($this->superAdmin->can('assignPermissions', $this->regularUser))->toBeTrue();
});

test('user inherits permission from role', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'escribiente']);
    $permission = Permission::create(['name' => 'crear_usuario']);
    $rol->permisos()->attach($permission);
    $user->roles()->attach($rol);
    $user->load('roles');

    expect($user->can('create', User::class))->toBeTrue();
});

test('user with direct permission can create', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => 'crear_usuario']);
    $user->permisosDirectos()->attach($permission);
    $user->load('permisosDirectos');

    expect($user->can('create', User::class))->toBeTrue();
});

test('user without permission cannot create', function () {
    $user = User::factory()->create();

    expect($user->can('create', User::class))->toBeFalse();
});

test('user can have multiple roles with different permissions', function () {
    $user = User::factory()->create();
    $rol1 = Rol::firstOrCreate(['name' => 'escribiente']);
    $rol2 = Rol::firstOrCreate(['name' => 'capitan_de_servicio']);
    $perm1 = Permission::create(['name' => 'crear_usuario']);
    $perm2 = Permission::create(['name' => 'editar_usuario']);
    $rol1->permisos()->attach($perm1);
    $rol2->permisos()->attach($perm2);
    $user->roles()->attach([$rol1->id, $rol2->id]);
    $user->load('roles');

    expect($user->can('create', User::class))->toBeTrue();
    expect($user->can('update', $user))->toBeTrue();
});
