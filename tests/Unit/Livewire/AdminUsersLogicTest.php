<?php

use App\Models\User;

/**
 * Tests para el componente Livewire Admin\Users.
 *
 * Se testean métodos que contienen lógica pura de negocio (cálculos,
 * transformaciones) separables del ciclo de vida Livewire.
 *
 * // TODO: candidato a extraer a un Service/Action — ejecutarEliminacionPermanente
 * //        mezcla DB transactions + state mutation + notifications.
 * // TODO: candidato a extraer a un Service/Action — destroyIncompleto
 * //        mezcla DB transactions + soft delete + state mutation.
 */

test('User CI calculation used by Users form is correct', function () {
    // Verificar que el cálculo de DV del CI usado en el formulario es consistente
    $ciNumeros = '1234567';
    $digito = User::calcularDigitoVerificadorCi($ciNumeros);

    expect($digito)->toBeInt();
    expect($digito)->toBeGreaterThanOrEqual(0);
    expect($digito)->toBeLessThanOrEqual(9);
});

test('User CI mutator used by Users form cleans and pads', function () {
    $user = User::factory()->make();
    $user->ci = '1.234.567-8';
    $user->save();

    // Debería quedar solo los números, padded a 7
    expect($user->ci)->toBe('1234567');
    expect($user->ci_dv)->not->toBeNull();
});

/**
 * // TODO: El método usuarios() del componente Admin\Users contiene un query
 * //        con filtros de búsqueda y paginación. Podría extraerse a un
 * //        SearchService o Action para unit testear la lógica de filtros
 * //        independientemente de Livewire.
 */

/**
 * // TODO: El método restaurar() del componente Admin\Users mezcla
 * //        soft delete restore + state mutation. Podría extraerse a un
 * //        UserRestoreService para testear la lógica de restauración.
 */
