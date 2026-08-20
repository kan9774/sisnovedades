<?php

use App\Jobs\EnviarNovedadGuardiaMail;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->guardia = Guard::factory()->create([
        'date'     => '2026-08-19',
        'status'   => 'closed',
    ]);

    $this->usuario = User::factory()->create([
        'email' => 'test@example.com',
    ]);
});

/**
 * Tests para el fix $messageId = null en EnviarNovedadGuardiaMail::handle().
 *
 * Bug original: si Mail::send() lanzaba una excepción antes de llegar a
 * $messageId = $mailable->messageId, la variable $messageId no existía y
 * el catch intentaba acceder a una variable no definida → PHP Warning
 * que propagaba el error al LoteJob (EnviarNovedadesGuardiaLoteJob).
 *
 * Fix: inicializar $messageId = null antes del try, y pasarlo al catch
 * como ?string. Así el registro en guardia_correos_fallidos queda con
 * message_id = null en vez de propagar el error.
 */

/**
 * Caso crítico: registrarFallo con message_id = null persiste correctamente.
 *
 * Verifica que el catch (con $messageId = null inicializada) inserta
 * un registro válido en guardia_correos_fallidos sin propagar el error.
 */
test('registrarFallo con message_id null persiste registro valido', function () {
    $exception = new RuntimeException('Connection refused');

    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('registrarFallo');
    $method->setAccessible(true);

    // Llama a registrarFallo con message_id = null (simula el catch)
    $method->invoke($job, $exception, null);

    $fallido = DB::table('guardia_correos_fallidos')->first();

    expect($fallido)->not->toBeNull();
    expect($fallido->guardia_id)->toBe($this->guardia->id);
    expect($fallido->user_id)->toBe($this->usuario->id);
    expect($fallido->email)->toBe('test@example.com');
    expect($fallido->message_id)->toBeNull();
    expect($fallido->tipo)->toBe('inmediato');
    expect($fallido->motivo)->toBe('❌ Error de conexión SMTP');
});

/**
 * Caso: registrarFallo con message_id = null y modos adjuntos/zip.
 */
test('registrarFallo con message_id null y adjuntos persiste modos', function () {
    $exception = new RuntimeException('Connection refused');

    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
        incluirAdjuntos: true,
        pdfContent: 'pdf-binary',
        enviarZip: false,
        zipContent: null,
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('registrarFallo');
    $method->setAccessible(true);

    $method->invoke($job, $exception, null);

    $fallido = DB::table('guardia_correos_fallidos')->first();

    expect($fallido)->not->toBeNull();
    expect((int) $fallido->con_adjuntos)->toBe(1);
    expect((int) $fallido->con_zip)->toBe(0);
});

/**
 * Caso: registrarFallo con message_id = null y modo zip.
 */
test('registrarFallo con message_id null y zip persiste modos', function () {
    $exception = new RuntimeException('Connection refused');

    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
        incluirAdjuntos: false,
        pdfContent: null,
        enviarZip: true,
        zipContent: 'zip-binary',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('registrarFallo');
    $method->setAccessible(true);

    $method->invoke($job, $exception, null);

    $fallido = DB::table('guardia_correos_fallidos')->first();

    expect($fallido)->not->toBeNull();
    expect((int) $fallido->con_adjuntos)->toBe(0);
    expect((int) $fallido->con_zip)->toBe(1);
});

/**
 * Caso: registrarFallo con message_id = null y ambos modos.
 */
test('registrarFallo con message_id null y ambos modos persiste', function () {
    $exception = new RuntimeException('Connection refused');

    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
        incluirAdjuntos: true,
        pdfContent: 'pdf-binary',
        enviarZip: true,
        zipContent: 'zip-binary',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('registrarFallo');
    $method->setAccessible(true);

    $method->invoke($job, $exception, null);

    $fallido = DB::table('guardia_correos_fallidos')->first();

    expect($fallido)->not->toBeNull();
    expect((int) $fallido->con_adjuntos)->toBe(1);
    expect((int) $fallido->con_zip)->toBe(1);
});

/**
 * Caso: registrarFallo con message_id no null (éxito parcial).
 *
 * Verifica que si el mailable se creó pero el envío falló,
 * el message_id se persiste correctamente.
 */
test('registrarFallo con message_id no null persiste message_id', function () {
    $exception = new RuntimeException('Connection refused');
    $messageId = 'test-uuid@localhost';

    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('registrarFallo');
    $method->setAccessible(true);

    $method->invoke($job, $exception, $messageId);

    $fallido = DB::table('guardia_correos_fallidos')->first();

    expect($fallido)->not->toBeNull();
    expect($fallido->message_id)->toBe($messageId);
});

/**
 * Caso: clasificarMotivo detecta error de conexión SMTP.
 */
test('clasificar motivo error conexion smtp', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, 'Connection refused');

    expect($motivo)->toBe('❌ Error de conexión SMTP');
});

/**
 * Caso: clasificarMotivo detecta timeout SMTP.
 */
test('clasificar motivo timeout smtp', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, 'Connection timed out');

    expect($motivo)->toBe('❌ Error de conexión SMTP');
});

/**
 * Caso: clasificarMotivo detecta autenticación fallida.
 */
test('clasificar motivo error autenticacion', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, '535 Authentication failed');

    expect($motivo)->toBe('❌ Error de autenticación SMTP');
});

/**
 * Caso: clasificarMotivo detecta casilla llena.
 */
test('clasificar motivo casilla llena', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, '552 Mailbox full');

    expect($motivo)->toBe('⚠️ Casilla llena (quota excedida)');
});

/**
 * Caso: clasificarMotivo detecta dirección inválida.
 */
test('clasificar motivo direccion invalida', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, '553 Invalid address');

    expect($motivo)->toBe('❌ Dirección de correo inválida');
});

/**
 * Caso: motivo desconocido genera prefijo "❓ ".
 */
test('clasificar motivo desconocido genera prefijo interrogacion', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, 'Some weird error');

    expect($motivo)->toContain('❓');
    expect($motivo)->toContain('Some weird error');
});

/**
 * Caso: clasificarMotivo detecta quota exceeded.
 */
test('clasificar motivo quota exceeded', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, 'Mailbox unavailable - over quota');

    expect($motivo)->toBe('⚠️ Casilla llena (quota excedida)');
});

/**
 * Caso: clasificarMotivo detecta unauthenticated.
 */
test('clasificar motivo unauthenticated', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, '535 Authentication required');

    expect($motivo)->toBe('❌ Error de autenticación SMTP');
});

/**
 * Caso: clasificarMotivo detecta syntax error.
 */
test('clasificar motivo syntax error', function () {
    $job = new EnviarNovedadGuardiaMail(
        guardia: $this->guardia,
        usuario: $this->usuario,
        nombreRemitente: 'Capitán Test',
    );

    $reflection = new \ReflectionClass($job);
    $method = $reflection->getMethod('clasificarMotivo');
    $method->setAccessible(true);

    $motivo = $method->invoke($job, '553 Invalid address syntax');

    expect($motivo)->toBe('❌ Dirección de correo inválida');
});
