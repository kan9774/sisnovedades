<?php

use App\Models\User;

/**
 * Tests para la lógica pura del componente GuardiaAcciones.
 *
 * // TODO: candidato a extraer a un Service/Action — ejecutarEliminacionNovedad
 * //        mezcla DB delete + state mutation + notifications.
 * // TODO: candidato a extraer a un Service/Action — confirmarEliminacion
 * //        mezcla authorization + state setup.
 */

test('GuardiaAcciones: lógica de autorización esMiembro', function () {
    // esMiembro es un método de Guard que GuardiaAcciones usa para autorización.
    // Testear aquí porque es lógica pura del modelo.
    $captain = User::factory()->create();
    $oficial = User::factory()->create();
    $escribiente = User::factory()->create();
    $unrelated = User::factory()->create();

    $guard = \App\Models\Guard::factory()->abierta()->create([
        'captain_id' => $captain->id,
        'oficer_id' => $oficial->id,
    ]);
    $guard->escribiente()->attach($escribiente->id, [
        'hora_inicio' => '08:00:00',
        'hora_fin' => '20:00:00',
    ]);

    expect($guard->esMiembro($captain))->toBeTrue();
    expect($guard->esMiembro($oficial))->toBeTrue();
    expect($guard->esMiembro($escribiente))->toBeTrue();
    expect($guard->esMiembro($unrelated))->toBeFalse();
    expect($guard->esMiembro(null))->toBeFalse();
});

/**
 * // TODO: El método guardarDestinatarioPdf() del componente PdfDestinatarios
 * //        mezcla DB attach + state mutation. Podría extraerse a un
 * //        PdfRecipientService para testear la lógica de adjuntar destinatarios.
 */

/**
 * // TODO: El método eliminarDestinatarioPdf() del componente PdfDestinatarios
 * //        mezcla DB detach + state mutation. Podría extraerse a un
 * //        PdfRecipientService.
 */
