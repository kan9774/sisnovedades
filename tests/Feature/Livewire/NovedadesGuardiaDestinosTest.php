<?php

namespace Tests\Feature\Livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Tests de validación del campo destinos en el componente novedades-guardia.
 *
 * Replican exactamente las reglas de validación del método guardar() en
 * resources/views/components/novedades-guardia/novedades-guardia.php,
 * para verificar que el closure condicional funciona correctamente.
 *
 * Bug original: con `public array $destinos = []` y la regla
 * `nullable|required_if:direction,Expedido|array|min:1`, el `nullable`
 * nunca se activaba porque `[]` no es `null`. Esto hacía que la validación
 * fallara incondicionalmente con "al menos 1 elementos", incluso para
 * direction='Recibido' donde el campo es invisible.
 *
 * Fix: reemplazo por closure que evalúa `$this->direction` en runtime.
 */
class NovedadesGuardiaDestinosTest extends TestCase
{
    use RefreshDatabase;

    private function validarDestinos(string $direction, array $destinos): array
    {
        $rules = [
            'destinos' => [
                'array',
                function ($attribute, $value, $fail) use ($direction) {
                    if ($direction === 'Expedido' && count($value) < 1) {
                        $fail('Debés seleccionar al menos un destino.');
                    }
                },
            ],
            'destinos.*' => 'string|max:255',
        ];

        $data = ['destinos' => $destinos];
        $validator = Validator::make($data, $rules);

        return $validator->fails() ? $validator->errors()->toArray() : [];
    }

    /**
     * Caso crítico: direction = 'Recibido' con destinos vacío []
     * NO debe fallar validación — el campo destinos es invisible en el form para Recibido.
     */
    public function test_recibido_con_destinos_vacios_pasa_validacion(): void
    {
        $errors = $this->validarDestinos('Recibido', []);
        $this->assertEmpty($errors, 'Recibido con destinos vacío NO debe fallar validación');
    }

    /**
     * direction = 'Expedido' con destinos vacío []
     * DEBE fallar validación — el closure exige al menos 1 destino.
     */
    public function test_expedido_con_destinos_vacios_falla_validacion(): void
    {
        $errors = $this->validarDestinos('Expedido', []);
        $this->assertNotEmpty($errors, 'Expedido con destinos vacío DEBE fallar validación');
        $this->assertArrayHasKey('destinos', $errors);
        $this->assertEquals('Debés seleccionar al menos un destino.', $errors['destinos'][0]);
    }

    /**
     * direction = 'Expedido' con 2+ destinos seleccionados
     * DEBE pasar validación correctamente.
     */
    public function test_expedido_con_multiples_destinos_pasa_validacion(): void
    {
        $errors = $this->validarDestinos('Expedido', ['Batallón 2', 'Batallón 5']);
        $this->assertEmpty($errors, 'Expedido con múltiples destinos NO debe fallar validación');
    }
}
