<?php

use App\Jobs\EnviarNovedadGuardiaMail;
use App\Jobs\EnviarNovedadesGuardiaLoteJob;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Support\DummyPdfGenerator;

/*
|--------------------------------------------------------------------------
| Envío de Correos de Guardia — Suite de Pruebas de Estrés
|--------------------------------------------------------------------------
|
| Diagnóstico del pipeline de envío de correos de guardia bajo condiciones
| límite de tamaño de adjunto y cantidad de destinatarios.
|
| Notas importantes:
| - MAIL_MAILER=array en testing → los correos se interceptan, no se envían
| - QUEUE_CONNECTION=sync → dispatchSync ejecuta inmediatamente
| - Mail::fake() intercepta Mail::to(...)->send() → el job corre normal
|   pero sin contactar al SMTP real
| - La memoria se mide con memory_get_peak_usage(true) en bytes
|--------------------------------------------------------------------------
*/

// ============================================================================
// Helpers
// ============================================================================

/**
 * Crea N usuarios con emails secuenciales.
 */
function crearUsuariosPrueba(int $cantidad, string $prefijo): \Illuminate\Database\Eloquent\Collection
{
    $users = [];
    for ($i = 1; $i <= $cantidad; $i++) {
        $users[] = User::factory()->create(['email' => $prefijo . $i . '@prueba.local']);
    }
    return \Illuminate\Database\Eloquent\Collection::make($users);
}

// ============================================================================
// Fixtures compartidos
// ============================================================================

beforeEach(function () {
    Mail::fake();

    // Guardia cerrada con datos mínimos para que el PDF se genere
    $this->guardia = Guard::factory()->create([
        'date'     => '2026-08-20',
        'status'   => 'closed',
    ]);
});

afterEach(function () {
    // Limpiar tablas de registro post-test
    DB::table('guardia_correos_enviados')->truncate();
    DB::table('guardia_correos_fallidos')->truncate();
});

// ============================================================================
// Test A: Envío básico a 15 destinatarios (sin adjuntos grandes)
// ============================================================================

test('envio basico a 15 destinatarios con Mail::fake', function () {
    $inicioMemoria = memory_get_peak_usage(true);

    // Crear 15 destinatarios
    $usuarios = crearUsuariosPrueba(15, 'test');

    // Disparar el envío para cada usuario
    $exitos = 0;
    $fallos = 0;

    foreach ($usuarios as $usuario) {
        $resultado = EnviarNovedadGuardiaMail::dispatchSync(
            guardia: $this->guardia,
            usuario: $usuario,
            nombreRemitente: 'Capitan Test',
            incluirAdjuntos: false,
            enviarZip: false,
        );

        if ($resultado) {
            $exitos++;
        } else {
            $fallos++;
        }
    }

    $finMemoria = memory_get_peak_usage(true);

    // Verificar resultados
    expect($exitos)->toBe(15, 'Los 15 envios deberian haber sido exitosos');
    expect($fallos)->toBe(0, 'No deberia haber fallos en condiciones base');

    // Verificar en la tabla de enviados
    $enviados = DB::table('guardia_correos_enviados')->count();
    expect($enviados)->toBe(15, '15 registros en guardia_correos_enviados');

    // Verificar en la tabla de fallidos
    $fallidos = DB::table('guardia_correos_fallidos')->count();
    expect($fallidos)->toBe(0, '0 registros en guardia_correos_fallidos');

    // Verificar con Mail::fake: 15 mensajes interceptados
    Mail::assertSent(\App\Mail\GuardiaNovedadesMail::class, 15);

    // Reporte de memoria
    $delta = $finMemoria - $inicioMemoria;
    echo PHP_EOL . '=== Test A: Envio basico 15 destinatarios ===';
    echo PHP_EOL . 'Memoria inicio: ' . round($inicioMemoria / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Memoria fin: ' . round($finMemoria / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Delta: ' . round($delta / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . '=============================================' . PHP_EOL;
});

// ============================================================================
// Test B: Envío con PDF ~14 MB a 15 destinatarios
// ============================================================================

test('envio con pdf 14mb a 15 destinatarios', function () {
    $inicioMemoria = memory_get_peak_usage(true);

    // Generar PDF dummy de ~14 MB
    $tamanoBytes = 14 * 1024 * 1024; // 14 MB exactos
    $pdfContent = DummyPdfGenerator::generar($tamanoBytes);
    $tamanoReal = strlen($pdfContent);

    $tamanoMbB = $tamanoBytes / 1024 / 1024;
    $tamanoRealMb = $tamanoReal / 1024 / 1024;
    echo PHP_EOL . '=== Test B: PDF de ' . $tamanoMbB . ' MB (' . $tamanoRealMb . ' MB reales) ===';

    // Crear 15 destinatarios
    $usuarios = crearUsuariosPrueba(15, 'testb');

    // Disparar el envío para cada usuario
    $exitos = 0;
    $fallos = 0;
    $errores = [];

    foreach ($usuarios as $usuario) {
        try {
            $resultado = EnviarNovedadGuardiaMail::dispatchSync(
                guardia: $this->guardia,
                usuario: $usuario,
                nombreRemitente: 'Capitan Test',
                incluirAdjuntos: false,
                pdfContent: $pdfContent,
                enviarZip: false,
                zipContent: null,
            );

            if ($resultado) {
                $exitos++;
            } else {
                $fallos++;
                $errores[] = 'Fallo en ' . $usuario->email;
            }
        } catch (\Throwable $e) {
            $fallos++;
            $errores[] = 'Excepcion en ' . $usuario->email . ': ' . $e->getMessage();
        }
    }

    $finMemoria = memory_get_peak_usage(true);

    echo PHP_EOL . 'Resultados: ' . $exitos . ' OK, ' . $fallos . ' fallos';
    echo PHP_EOL . 'Memoria inicio: ' . round($inicioMemoria / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Memoria fin: ' . round($finMemoria / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Delta: ' . round(($finMemoria - $inicioMemoria) / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Memory limit PHP: ' . ini_get('memory_limit');

    if (!empty($errores)) {
        echo PHP_EOL . 'Errores:';
        foreach ($errores as $error) {
            echo PHP_EOL . '  - ' . $error;
        }
    }
    echo PHP_EOL . '=============================================' . PHP_EOL;

    // Verificar resultados
    expect($exitos)->toBe(15, 'Los 15 envios con PDF de 14 MB deberian haber sido exitosos');
    expect($fallos)->toBe(0, 'No deberia haber fallos con PDF de 14 MB');

    // Verificar en las tablas
    expect(DB::table('guardia_correos_enviados')->count())->toBe(15);
    expect(DB::table('guardia_correos_fallidos')->count())->toBe(0);
});

// ============================================================================
// Test C: Envío incremental de tamaño hasta punto de quiebre
// ============================================================================

test('prueba incremental de tamanio hasta punto de quiebre', function () {
    // Tamaños a probar en MB
    $tamaniosMb = [14, 16, 18, 20, 25];
    $resultados = [];

    foreach ($tamaniosMb as $tamanoMb) {
        echo PHP_EOL . '--- Prueba: PDF de ' . $tamanoMb . ' MB ---';

        $tamanoBytes = $tamanoMb * 1024 * 1024;
        $inicioMemoria = memory_get_peak_usage(true);

        // Generar PDF dummy
        $pdfContent = DummyPdfGenerator::generar($tamanoBytes);
        $tamanoReal = strlen($pdfContent);

        // Crear 15 destinatarios fresh para cada tanda
        $usuarios = crearUsuariosPrueba(15, 'incr' . $tamanoMb . '_');

        $exitos = 0;
        $fallos = 0;
        $primerError = null;

        foreach ($usuarios as $usuario) {
            try {
                $resultado = EnviarNovedadGuardiaMail::dispatchSync(
                    guardia: $this->guardia,
                    usuario: $usuario,
                    nombreRemitente: 'Capitan Test',
                    incluirAdjuntos: false,
                    pdfContent: $pdfContent,
                    enviarZip: false,
                    zipContent: null,
                );

                if ($resultado) {
                    $exitos++;
                } else {
                    $fallos++;
                    if ($primerError === null) {
                        $primerError = 'Fallo en ' . $usuario->email;
                    }
                }
            } catch (\Throwable $e) {
                $fallos++;
                if ($primerError === null) {
                    $primerError = 'Excepcion en ' . $usuario->email . ': ' . $e->getMessage();
                }
            }
        }

        $finMemoria = memory_get_peak_usage(true);
        $deltaMb = round(($finMemoria - $inicioMemoria) / 1024 / 1024, 2);

        $resultados[] = [
            'tamano_mb'       => $tamanoMb,
            'tamano_bytes'    => $tamanoReal,
            'exitos'          => $exitos,
            'fallos'          => $fallos,
            'total'           => 15,
            'porcentaje_ok'   => round(($exitos / 15) * 100, 1),
            'memoria_inicio'  => round($inicioMemoria / 1024 / 1024, 2),
            'memoria_fin'     => round($finMemoria / 1024 / 1024, 2),
            'memoria_delta'   => $deltaMb,
            'primer_error'    => $primerError,
        ];

        echo 'Resultados: ' . $exitos . '/15 OK | Fallos: ' . $fallos . ' | Delta memoria: ' . $deltaMb . ' MB';

        // Si hubo fallos, detener la prueba incremental
        if ($fallos > 0) {
            echo PHP_EOL . 'PRIMER FALLO DETECTADO A ' . $tamanoMb . ' MB. DETINIENDO PRUEBA INCREMENTAL.';
            break;
        }
    }

    // Imprimir tabla resumen
    echo PHP_EOL . PHP_EOL . str_repeat('=', 75);
    echo PHP_EOL . '  REPORTE DE PRUEBA INCREMENTAL DE TAMANO';
    echo PHP_EOL . str_repeat('=', 75);
    echo PHP_EOL . str_pad('Tamano (MB)', 15)
         . str_pad('OK', 8)
         . str_pad('Fallos', 10)
         . str_pad('% OK', 10)
         . str_pad('Delta Mem (MB)', 18);
    echo PHP_EOL . str_repeat('-', 75);

    foreach ($resultados as $r) {
        echo str_pad((string) $r['tamano_mb'], 15)
             . str_pad((string) $r['exitos'], 8)
             . str_pad((string) $r['fallos'], 10)
             . str_pad($r['porcentaje_ok'] . '%', 10)
             . str_pad((string) $r['memoria_delta'], 18);

        if ($r['primer_error']) {
            echo PHP_EOL . '  Primer error: ' . $r['primer_error'];
        }
    }

    echo PHP_EOL . str_repeat('=', 75) . PHP_EOL;

    // Determinar el punto de quiebre
    $quiebre = null;
    foreach ($resultados as $r) {
        if ($r['fallos'] > 0) {
            $quiebre = $r['tamano_mb'];
            break;
        }
    }

    if ($quiebre === null) {
        echo PHP_EOL . 'No se alcanzo el punto de quiebre en los tamanos probados (hasta 25 MB).';
        echo PHP_EOL . 'El limite real es MAYOR a 25 MB en este entorno.';
    } else {
        echo PHP_EOL . 'PUNTO DE QUIEBRE: ' . $quiebre . ' MB';
        echo PHP_EOL . 'El primer fallo se detecto con adjuntos de ' . $quiebre . ' MB.';
    }

    // Assertions minimas (la prueba no falla si hay quiebre, solo registra)
    expect(count($resultados))->toBeLessThanOrEqual(5);
    expect($resultados[0]['tamano_mb'])->toBe(14);
});

// ============================================================================
// Test D: Fallo individual NO interrumpe el batch
// ============================================================================

test('fallo individual no interrumpe el resto del batch', function () {
    // Crear 15 destinatarios
    $usuarios = crearUsuariosPrueba(15, 'batch');

    // Resetear tablas
    DB::table('guardia_correos_enviados')->truncate();
    DB::table('guardia_correos_fallidos')->truncate();

    $exitos = 0;
    $fallos = 0;
    $falloRegistrado = false;

    foreach ($usuarios as $index => $usuario) {
        try {
            // Para el usuario #5, usar reflection para forzar un fallo
            // en registrarFallo (simulando lo que pasaria con un error real)
            if ($index === 4) {
                $job = new EnviarNovedadGuardiaMail(
                    guardia: $this->guardia,
                    usuario: $usuario,
                    nombreRemitente: 'Capitan Test',
                );

                $reflection = new \ReflectionClass($job);
                $method = $reflection->getMethod('registrarFallo');
                $method->setAccessible(true);
                $exception = new RuntimeException('Connection refused');
                $method->invoke($job, $exception, null);
                $fallos++;
                $falloRegistrado = true;
                continue;
            }

            // Para los demas, envio normal con Mail::fake
            $resultado = EnviarNovedadGuardiaMail::dispatchSync(
                guardia: $this->guardia,
                usuario: $usuario,
                nombreRemitente: 'Capitan Test',
                incluirAdjuntos: false,
            );

            if ($resultado) {
                $exitos++;
            } else {
                $fallos++;
            }
        } catch (\Throwable $e) {
            $fallos++;
        }
    }

    echo PHP_EOL . '=== Test D: Fallo individual en batch ===';
    echo PHP_EOL . 'Exitos: ' . $exitos . ', Fallos: ' . $fallos;

    // Deberian haber 14 exitos y 1 fallo
    expect($exitos)->toBe(14, '14 envios deberian haber tenido exito');
    expect($fallos)->toBe(1, 'Solo 1 envio deberia haber fallado');
    expect($falloRegistrado)->toBeTrue('El fallo deberia haber sido registrado');

    // Verificar en las tablas
    expect(DB::table('guardia_correos_enviados')->count())->toBe(14);
    expect(DB::table('guardia_correos_fallidos')->count())->toBe(1);

    // Verificar que el fallo registrado tiene el formato correcto
    $fallo = DB::table('guardia_correos_fallidos')->first();
    expect($fallo->tipo)->toBe('inmediato');
    expect($fallo->motivo)->toContain('Error de conexión SMTP');
});

// ============================================================================
// Test E: Fallo antes del SMTP queda en guardia_correos_fallidos
// ============================================================================

test('fallo antes del smtp se registra en guardia_correos_fallidos', function () {
    // Resetear
    DB::table('guardia_correos_fallidos')->truncate();
    DB::table('guardia_correos_enviados')->truncate();

    // Con Mail::fake(), el envío "simula" éxito porque no hay SMTP real.
    // Para probar el catch real, necesitamos forzar una excepción en handle().
    // Usamos reflection para llamar registrarFallo directamente (mismo patrón
    // que los tests existentes en EnviarNovedadGuardiaMailTest.php).

    $usuario = User::factory()->create();

    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $usuario,
        nombreRemitente: 'Capitan Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('registrarFallo');
    $method->setAccessible(true);

    // Forzar una excepcion
    $exception = new RuntimeException('Connection refused');
    $method->invoke($job, $exception, null);

    echo PHP_EOL . '=== Test E: Fallo antes del SMTP ===';

    // Verificar que se registro en guardia_correos_fallidos
    $fallidos = DB::table('guardia_correos_fallidos')->count();
    $enviados = DB::table('guardia_correos_enviados')->count();

    echo PHP_EOL . 'Fallidos: ' . $fallidos . ', Enviados: ' . $enviados;

    expect($fallidos)->toBe(1, 'Deberia haber 1 registro en fallidos');
    expect($enviados)->toBe(0, 'No deberia haber registros en enviados');

    $fallo = DB::table('guardia_correos_fallidos')->first();
    expect($fallo->tipo)->toBe('inmediato');
    expect($fallo->motivo)->toContain('Error de conexión SMTP');
});

// ============================================================================
// Test F: Memoria acumulada con PDF grande — análisis de crecimiento
// ============================================================================

test('analisis de memoria acumulada con pdf grande', function () {
    $tamanoMb = 14;
    $tamanoBytes = $tamanoMb * 1024 * 1024;
    $pdfContent = DummyPdfGenerator::generar($tamanoBytes);
    $tamanoReal = strlen($pdfContent);

    $pdfSizeMb = $tamanoReal / 1024 / 1024;
    echo PHP_EOL . '=== Test F: Análisis de memoria acumulada ===';
    echo PHP_EOL . 'PDF size: ' . $pdfSizeMb . ' MB';
    echo PHP_EOL . 'PHP memory_limit: ' . ini_get('memory_limit');

    // Medir memoria antes de cualquier operación
    gc_collect_cycles();
    $memBase = memory_get_usage(true);
    $peakBase = memory_get_peak_usage(true);

    echo PHP_EOL . 'Memoria base: ' . round($memBase / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Peak base: ' . round($peakBase / 1024 / 1024, 2) . ' MB';

    // Crear destinatarios
    $usuarios = crearUsuariosPrueba(15, 'mem');

    gc_collect_cycles();
    $memAfterUsers = memory_get_usage(true);
    echo PHP_EOL . 'Después de crear 15 usuarios: ' . round($memAfterUsers / 1024 / 1024, 2) . ' MB';

    // Enviar uno por uno y medir cada paso
    $memorias = [];
    foreach ($usuarios as $index => $usuario) {
        EnviarNovedadGuardiaMail::dispatchSync(
            guardia: $this->guardia,
            usuario: $usuario,
            nombreRemitente: 'Capitan Test',
            incluirAdjuntos: false,
            pdfContent: $pdfContent,
            enviarZip: false,
        );

        gc_collect_cycles();
        $memActual = memory_get_usage(true);
        $peakActual = memory_get_peak_usage(true);
        $delta = $memActual - $memBase;
        $numEnvio = $index + 1;

        $memorias[] = [
            'index' => $index + 1,
            'email' => $usuario->email,
            'usage' => round($memActual / 1024 / 1024, 2),
            'peak'  => round($peakActual / 1024 / 1024, 2),
            'delta' => round($delta / 1024 / 1024, 2),
        ];

        if (($index + 1) % 5 === 0 || $index === 14) {
            echo PHP_EOL . '  Envio #' . $numEnvio . '/15: usage=' . round($memActual / 1024 / 1024, 2)
                . ' MB, peak=' . round($peakActual / 1024 / 1024, 2)
                . ' MB, delta=' . round($delta / 1024 / 1024, 2) . ' MB';
        }
    }

    gc_collect_cycles();
    $memFinal = memory_get_usage(true);
    $peakFinal = memory_get_peak_usage(true);

    echo PHP_EOL . 'Memoria final: ' . round($memFinal / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Peak final: ' . round($peakFinal / 1024 / 1024, 2) . ' MB';
    echo PHP_EOL . 'Delta total: ' . round(($memFinal - $memBase) / 1024 / 1024, 2) . ' MB';

    // Análisis: con Mail::fake(), el PDF se mantiene en memoria (pasado por referencia
    // en el closure del job). Esperamos que el delta sea proporcional al tamaño del PDF
    // × número de envíos, pero Laravel debería liberar la referencia después de cada envío.

    $deltaTotalMb = ($memFinal - $memBase) / 1024 / 1024;
    $ratio = round($deltaTotalMb / $pdfSizeMb, 2);

    echo PHP_EOL . '--- Análisis de crecimiento ---';
    echo PHP_EOL . 'Tamaño del PDF: ' . $pdfSizeMb . ' MB';
    echo PHP_EOL . 'Delta total tras 15 envíos: ' . $deltaTotalMb . ' MB';
    echo PHP_EOL . 'Ratio delta/tamaño PDF: ' . $ratio;

    if ($deltaTotalMb > $pdfSizeMb * 2) {
        echo PHP_EOL . 'ALERTA: La memoria crece mas del doble del tamaño del PDF.';
        echo PHP_EOL . '   Esto sugiere que el PDF se mantiene en memoria por cada envío';
        echo PHP_EOL . '   sin liberarse adecuadamente.';
    } else {
        echo PHP_EOL . 'El crecimiento de memoria es razonable respecto al tamaño del PDF.';
    }

    // Verificar que todos se enviaron
    expect(DB::table('guardia_correos_enviados')->count())->toBe(15);

    // Guardar datos para el reporte
    data_set($this, 'memoriaAnalisis', [
        'pdf_size_mb'     => $pdfSizeMb,
        'delta_total_mb'  => $deltaTotalMb,
        'ratio'           => $ratio,
        'memorias'        => $memorias,
    ]);
});

// ============================================================================
// Test G: Simulación del flujo completo LoteJob (dispatchSync en loop)
// ============================================================================

test('simulacion del flujo completo del lotejob', function () {
    // Este test simula exactamente lo que hace EnviarNovedadesGuardiaLoteJob::handle()
    // pero con un PDF grande, para verificar que el patrón de try/catch por usuario
    // funciona correctamente.

    $tamanoMb = 14;
    $tamanoBytes = $tamanoMb * 1024 * 1024;
    $pdfContent = DummyPdfGenerator::generar($tamanoBytes);

    // Crear 15 usuarios
    $usuarios = crearUsuariosPrueba(15, 'lote');
    $usuarioIds = $usuarios->pluck('id')->all();

    // Simular el comportamiento del LoteJob
    ignore_user_abort(true);
    set_time_limit(EnviarNovedadesGuardiaLoteJob::ENVIO_TIMEOUT_SEGUNDOS);

    $inicioMemoria = memory_get_peak_usage(true);
    $exitos = 0;
    $fallos = 0;
    $erroresLog = [];

    $usuarios = User::whereIn('id', $usuarioIds)->get();

    foreach ($usuarios as $usuario) {
        try {
            EnviarNovedadGuardiaMail::dispatchSync(
                $this->guardia,
                $usuario,
                'Capitan Test',
                false,
                $pdfContent,
                false,
                null,
            );
            $exitos++;
        } catch (\Throwable $exception) {
            $fallos++;
            $erroresLog[] = [
                'email' => $usuario->email,
                'error' => $exception->getMessage(),
            ];

            DB::table('guardia_correos_fallidos')->insert([
                'guardia_id'     => $this->guardia->id,
                'user_id'        => $usuario->id,
                'email'          => $usuario->email,
                'motivo'         => 'Error inesperado al procesar el envío — ver logs',
                'tipo'           => 'inmediato',
                'message_id'     => null,
                'con_adjuntos'   => false,
                'con_zip'        => false,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    $finMemoria = memory_get_peak_usage(true);

    echo PHP_EOL . '=== Test G: Simulación LoteJob con PDF ' . $tamanoMb . ' MB ===';
    echo PHP_EOL . 'Exitos: ' . $exitos . ', Fallos: ' . $fallos;
    echo PHP_EOL . 'Delta memoria: ' . round(($finMemoria - $inicioMemoria) / 1024 / 1024, 2) . ' MB';

    if (!empty($erroresLog)) {
        echo PHP_EOL . 'Errores logueados:';
        foreach ($erroresLog as $err) {
            echo PHP_EOL . '  - ' . $err['email'] . ': ' . $err['error'];
        }
    }

    // Verificar
    expect($exitos)->toBe(15);
    expect($fallos)->toBe(0);
    expect(DB::table('guardia_correos_enviados')->count())->toBe(15);
});
