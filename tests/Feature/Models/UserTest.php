<?php

use App\Enums\UserStatus;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('fortify.email_verification_enabled', false);
});

test('user can be created via factory', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->not->toBeEmpty();
    expect($user->email)->not->toBeEmpty();
    expect($user->password)->not->toBe('password');
});

test('user has default status active', function () {
    $user = User::factory()->create(['status' => UserStatus::Active]);

    expect($user->status)->toBe(UserStatus::Active);
});

test('user is not super admin by default', function () {
    $user = User::factory()->create();

    expect($user->isSuperAdmin())->toBeFalse();
});

test('isSuperAdmin returns true for super admin', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    expect($user->isSuperAdmin())->toBeTrue();
});

test('isAdmin returns true for admin role', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'admin']);
    $user->roles()->attach($rol);

    expect($user->isAdmin())->toBeTrue();
});

test('isAdmin returns true for super admin without admin role', function () {
    $user = User::factory()->create(['is_super_admin' => true]);

    expect($user->isAdmin())->toBeTrue();
});

test('isAdmin returns false for regular user', function () {
    $user = User::factory()->create();

    expect($user->isAdmin())->toBeFalse();
});

test('tieneRol returns true when user has the role', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'escribiente']);
    $user->roles()->attach($rol);

    expect($user->tieneRol('escribiente'))->toBeTrue();
});

test('tieneRol is case insensitive', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'Escribiente']);
    $user->roles()->attach($rol);

    expect($user->tieneRol('escribiente'))->toBeTrue();
    expect($user->tieneRol('ESCRIBIENTE'))->toBeTrue();
});

test('tieneRol returns false when user does not have the role', function () {
    $user = User::factory()->create();

    expect($user->tieneRol('admin'))->toBeFalse();
});

test('isOficialDia checks oficial_de_dia role', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'oficial_de_dia']);
    $user->roles()->attach($rol);

    expect($user->isOficialDia())->toBeTrue();
    expect($user->isCapitan())->toBeFalse();
    expect($user->isEscribiente())->toBeFalse();
});

test('isCapitan checks capitan_de_servicio role', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'capitan_de_servicio']);
    $user->roles()->attach($rol);

    expect($user->isCapitan())->toBeTrue();
    expect($user->isOficialDia())->toBeFalse();
});

test('isEscribiente checks escribiente role', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'escribiente']);
    $user->roles()->attach($rol);

    expect($user->isEscribiente())->toBeTrue();
    expect($user->isCapitan())->toBeFalse();
});

test('hasVerifiedEmail returns true for super admin even without verified date', function () {
    $user = User::factory()->unverified()->create(['is_super_admin' => true]);

    expect($user->hasVerifiedEmail())->toBeTrue();
});

test('hasVerifiedEmail returns false for unverified regular user', function () {
    $user = User::factory()->unverified()->create();

    expect($user->hasVerifiedEmail())->toBeFalse();
});

test('hasVerifiedEmail returns true for verified user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    expect($user->hasVerifiedEmail())->toBeTrue();
});

test('sendEmailVerificationNotification does nothing when disabled', function () {
    Config::set('fortify.email_verification_enabled', false);

    $user = User::factory()->unverified()->create();
    $user->sendEmailVerificationNotification();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email_verified_at' => null,
    ]);
});

test('calcularDigitoVerificadorCi returns correct digit', function () {
    expect(User::calcularDigitoVerificadorCi('1234567'))->toBeInt();
    expect(User::calcularDigitoVerificadorCi('0000000'))->toBe(0);
});

test('setCiAttribute cleans and formats CI', function () {
    $user = User::factory()->create();
    $user->ci = '1.234.567';
    $user->save();

    expect($user->ci)->toBe('1234567');
    expect($user->ci_dv)->not->toBeNull();
});

test('setCiAttribute handles 8 digit CI by trimming last digit', function () {
    $user = User::factory()->create();
    $user->ci = '12345678';
    $user->save();

    expect($user->ci)->toBe('1234567');
});

test('setCiAttribute handles blank value', function () {
    $user = User::factory()->create(['ci' => '1234567']);
    $user->ci = null;
    $user->save();

    expect($user->ci)->toBeNull();
    expect($user->ci_dv)->toBeNull();
});

test('setCiAttribute pads short CI with leading zeros', function () {
    $user = User::factory()->create();
    $user->ci = '123';
    $user->save();

    expect($user->ci)->toBe('0000123');
});

test('getCiCompletoAttribute returns CI with DV', function () {
    $user = User::factory()->create();
    $user->ci = '1234567';
    $user->save();

    $ciCompleto = $user->ci_completo;

    expect($ciCompleto)->toContain('-');
    expect(explode('-', $ciCompleto)[0])->toBe('1234567');
});

test('getCiCompletoAttribute returns null when no CI', function () {
    $user = User::factory()->create();

    expect($user->ci_completo)->toBeNull();
});

test('getCiFormateadoAttribute returns formatted CI with dots', function () {
    $user = User::factory()->create();
    $user->ci = '1234567';
    $user->save();

    $formateado = $user->ci_formateado;

    expect($formateado)->toContain('.');
    expect($formateado)->toContain('-');
});

test('getCiFormateadoAttribute returns null when no CI', function () {
    $user = User::factory()->create();

    expect($user->ci_formateado)->toBeNull();
});

test('initials returns first and last character of name', function () {
    $user = User::factory()->create(['name' => 'Carlos Pereyra']);

    expect($user->initials())->toBe('CP');
});

test('initials returns single initial for single character name', function () {
    $user = User::factory()->create(['name' => 'A']);

    expect($user->initials())->toBe('A');
});

test('roles relationship returns attached roles', function () {
    $user = User::factory()->create();
    $rol1 = Rol::firstOrCreate(['name' => 'admin']);
    $rol2 = Rol::firstOrCreate(['name' => 'escribiente']);
    $user->roles()->attach([$rol1->id, $rol2->id]);

    expect($user->roles)->toHaveCount(2);
    expect($user->roles->pluck('name')->toArray())->toContain('admin');
    expect($user->roles->pluck('name')->toArray())->toContain('escribiente');
});

test('getRolAttribute returns first role', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'capitan_de_servicio']);
    $user->roles()->attach($rol);

    expect($user->rol)->toBeInstanceOf(Rol::class);
    expect($user->rol->name)->toBe('capitan_de_servicio');
});

test('getRolNameAttribute returns formatted role name', function () {
    $user = User::factory()->create();
    $rol = Rol::firstOrCreate(['name' => 'capitan_de_servicio']);
    $user->roles()->attach($rol);

    expect($user->rol_name)->toBe('capitan de servicio');
});

test('getRolNameAttribute returns Sin rol when no role', function () {
    $user = User::factory()->create();

    expect($user->rol_name)->toBe('Sin rol');
});

test('getRolesListAttribute returns comma separated role names', function () {
    $user = User::factory()->create();
    $rol1 = Rol::firstOrCreate(['name' => 'admin']);
    $rol2 = Rol::firstOrCreate(['name' => 'escribiente']);
    $user->roles()->attach([$rol1->id, $rol2->id]);

    $list = $user->roles_list;

    expect($list)->toContain('admin');
    expect($list)->toContain('escribiente');
});

test('getRolesListAttribute returns Sin rol when no roles', function () {
    $user = User::factory()->create();

    expect($user->roles_list)->toBe('Sin rol');
});

test('getGradeAttribute returns grade name', function () {
    $grado = \App\Models\Grado::create(['nombre' => 'Sargento']);
    $user = User::factory()->create(['grado_id' => $grado->id]);

    expect($user->grade)->toBe('Sargento');
});

test('getGradeAttribute returns null when no grade', function () {
    $user = User::factory()->create();

    expect($user->grade)->toBeNull();
});

test('password is automatically hashed', function () {
    $user = User::factory()->create(['password' => 'mypassword']);

    expect($user->getOriginal('password'))->not->toBe('mypassword');
    expect(\Hash::check('mypassword', $user->password))->toBeTrue();
});

test('soft delete and restore works', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $user->delete();

    expect(User::find($userId))->toBeNull();
    expect(User::withTrashed()->find($userId))->not->toBeNull();

    $user->restore();

    expect(User::find($userId))->not->toBeNull();
});
