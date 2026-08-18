<?php

use App\Models\User;

/**
 * Tests para la lógica pura del componente Livewire Admin\UserForm.
 *
 * Los métodos evaluados son puros en el sentido de que transforman datos
 * o calculan valores sin depender del ciclo de vida Livewire.
 */
test('calcularDigitoVerificador returns correct CI check digit', function () {
    // El método calcula el dígito verificador del CI uruguayo
    // Usando coeficientes [2,9,8,7,6,3,4]
    expect(User::calcularDigitoVerificadorCi('1234567'))->toBeInt();
    expect(User::calcularDigitoVerificadorCi('0000000'))->toBe(0);
});

test('User mutator setCiAttribute cleans and pads CI', function () {
    $user = User::factory()->make();

    // Clean dots and dashes
    $user->ci = '1.234.567';
    $user->save();
    expect($user->ci)->toBe('1234567');

    // Compute DV
    expect($user->ci_dv)->not->toBeNull();
});

test('User mutator setCiAttribute pads short CI with leading zeros', function () {
    $user = User::factory()->make();
    $user->ci = '123';
    $user->save();

    expect($user->ci)->toBe('0000123');
    expect(strlen($user->ci))->toBe(7);
});

test('User mutator setCiAttribute handles 8 digit CI by trimming last digit', function () {
    $user = User::factory()->make();
    $user->ci = '12345678';
    $user->save();

    expect($user->ci)->toBe('1234567');
});

test('User mutator setCiAttribute handles null/blank', function () {
    $user = User::factory()->create(['ci' => '1234567']);
    $user->ci = null;
    $user->save();

    expect($user->ci)->toBeNull();
    expect($user->ci_dv)->toBeNull();
});

test('User ci_completo includes dash separator', function () {
    $user = User::factory()->make();
    $user->ci = '1234567';
    $user->save();

    expect($user->ci_completo)->toContain('-');
    expect(explode('-', $user->ci_completo)[0])->toBe('1234567');
});

test('User ci_formateado includes dots and dash', function () {
    $user = User::factory()->make();
    $user->ci = '1234567';
    $user->save();

    expect($user->ci_formateado)->toContain('.');
    expect($user->ci_formateado)->toContain('-');
});

test('User ci_formateado returns null when no CI', function () {
    $user = User::factory()->make();
    expect($user->ci_formateado)->toBeNull();
});

test('User initials returns first and last character', function () {
    $user = User::factory()->make(['name' => 'Carlos Pereyra']);
    expect($user->initials())->toBe('CP');
});

test('User initials handles single character name', function () {
    $user = User::factory()->make(['name' => 'A']);
    expect($user->initials())->toBe('A');
});
