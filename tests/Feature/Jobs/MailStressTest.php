<?php

use App\Mail\GuardiaNovedadesMail;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/*
 * Stress test: envío real contra el mailer test_webmail (SMTP de guarnición).
 *
 * Objetivo: descubrir el límite de tamaño que el servidor SMTP/Zimbra acepta
 * antes de rechazar mensajes, midiendo el tamaño del PDF adjunto que
 * enviamos (no el tamaño del cuerpo del mensaje).
 *
 * Configuración:
 *   - Mailer: test_webmail (config/mail.php)
 *   - Destinatarios: 15 aliases +testN sobre s1bcom1@ejercito.mil.uy
 *   - Adjunto: PDF generado con DomPDF + padding binario para alcanzar tamaños objetivo
 *   - Captura: código de respuesta SMTP (5xx/4xx) si el servidor rechaza
 *
 * Uso:
 *   php artisan test --filter MailStressTest
 *
 *   Para solo verificar la conexión (sin enviar 15 correos):
 *   MAIL_STRESS_DRY_RUN=1 php artisan test --filter MailStressTest
 */

beforeEach(function () {
    // Guard con novedades para que el PDF tenga contenido real
    $this->guardia = Guard::factory()->create([
        'date'     => '2026-08-19',
        'status'   => 'closed',
        'notes'    => 'Guardia de prueba para estrés de mailer.',
    ]);

    // User con password para autenticación si hace falta en policies
    $this->usuario = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    // Destinatarios: 15 aliases +testN sobre la cuenta test_webmail
    // Asumimos que el dominio soporta plus addressing (Zimbra sí lo soporta)
    // Todos caen en la misma bandeja de s1bcom1@ejercito.mil.uy
    $this->destinatarios = [];
    for ($i = 1; $i <= 15; $i++) {
        $this->destinatarios[] = 's1bcom1+test' . $i . '@ejercito.mil.uy';
    }
});

/**
 * Genera un PDF base pequeño con DomPDF (~50-100 KB).
 *
 * El stress test usa paddedPdf() para alcanzar los tamaños objetivo.
 * Generar un PDF real de 14 MB con DomPDF excedería la memoria PHP (512 MB)
 * porque DomPDF intenta renderizar todo en memoria.
 *
 * @return string Binario del PDF base
 */
function generarPdfBase(): string
{
    $dompdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<html><body></body></html>');

    // 1000 filas × ~60 bytes ≈ 60 KB → DomPDF lo expande a ~50-100 KB
    $filas = '';
    for ($i = 0; $i < 1000; $i++) {
        $filas .= '<tr><td style="font-size: 10px;">Novedad repetida para aumentar tamaño. Detalle extenso de la novedad. Unidad: Batallón 1. Hora: 08:00. Estado: Normal. Observaciones: Sin novedades relevantes.</td></tr>';
    }

    $html = '<html><head><meta charset="utf-8"></head><body style="font-family: Arial, sans-serif; font-size: 12px;"><h1>Novedades de Guardia</h1><p>Fecha: 19/08/2026</p><p>Estado: Cerrada</p><table border="1" cellpadding="4" cellspacing="0" width="100%"><thead><tr style="background: #0B2545; color: #FFD200;"><th>Hora</th><th>Detalle</th><th>Unidad</th></tr></thead><tbody>' . $filas . '</tbody></table></body></html>';

    $dompdf->loadHTML($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return (string) $dompdf->output();
}

/**
 * Padded PDF: toma un PDF existente y le agrega bytes al final para alcanzar
 * el tamaño objetivo. El PDF queda técnicamente corrupto, pero es suficiente
 * para stress test del mailer (lo que importa es el tamaño del payload, no
 * la integridad del archivo).
 *
 * @param string $pdfBase PDF original
 * @param int $tamañoObjetivo Tamaño objetivo en bytes
 * @return string PDF padded
 */
function paddedPdf(string $pdfBase, int $tamañoObjetivo): string
{
    $tamañoActual = strlen($pdfBase);

    if ($tamañoActual >= $tamañoObjetivo) {
        return $pdfBase;
    }

    $bytesFaltantes = $tamañoObjetivo - $tamañoActual;

    // Generar padding: bloques de 1024 bytes de datos pseudo-aleatorios
    $padding = '';
    while (strlen($padding) < $bytesFaltantes) {
        $padding .= random_bytes(min(1024, $bytesFaltantes - strlen($padding)));
    }

    return $pdfBase . substr($padding, 0, $bytesFaltantes);
}

/**
 * Extrae el código de respuesta SMTP del mensaje de una excepción.
 *
 * Busca patrones como "552", "421", "550", "554", etc.
 *
 * @return string|null Código SMTP o null si no se encuentra
 */
function extraerCodigoSmtp(\Throwable $e): ?string
{
    // Pattern: código de 3 dígitos seguido de espacio y mensaje
    // Ej: "552 Message size exceeds fixed maximum message size"
    if (preg_match('/\b([45]\d{2})\b/', $e->getMessage(), $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Clasifica el tipo de fallo según el código SMTP y el mensaje.
 */
function clasificarFalloSmtp(?string $codigo, string $mensaje): string
{
    if ($codigo === null) {
        return 'Sin código SMTP — ' . Str::limit($mensaje, 100);
    }

    return match ((int) $codigo) {
        421 => 'Servidor saturado (421 — try again later)',
        450 => 'Bandeja temporalmente no disponible (450)',
        451 => 'Error local — operación cancelada (451)',
        452 => 'Espacio insuficiente en el servidor (452)',
        500 => 'Error de sintaxis — comando no reconocido (500)',
        501 => 'Error de sintaxis en parámetros (501)',
        503 => 'Secuencia de comandos incorrecta (503)',
        550 => 'Destinatario no encontrado / retransmisión no permitida (550)',
        551 => 'Usuario no local — pruebe <dirección> (551)',
        552 => 'Exceso de tamaño de mensaje (552) ← límite del servidor',
        553 => 'Dirección de correo no válida (553)',
        554 => 'Transacción fallida — error no especificado (554)',
        558 => 'Reenvío prohibido (558)',
        default => "Código SMTP $codigo — $mensaje",
    };
}

test('stress test: envío incremental con mailer real test_webmail', function () {
    // Aumentar memory_limit: codificar PDFs grandes en Base64 (×3) requiere
    // ~84MB para un PDF de 28MB. Con overhead de Laravel + DomPDF, 512MB no basta.
    ini_set('memory_limit', '1024M');

    // Configurar mailer test_webmail en el entorno de testing.
    // phpunit.xml pone APP_ENV=testing → carga .env.testing (sin credenciales).
    // Usamos Config::set() que persiste en el container del test.
    Config::set('mail.mailers.test_webmail', [
        'transport'  => 'smtp',
        'host'       => 'webmail.ejercito.mil.uy',
        'port'       => 587,
        'encryption' => 'tls',
        'username'   => 's1bcom1@ejercito.mil.uy',
        'password'   => 'HPDNTCKGJXPDCLKI',
        'timeout'    => null,
    ]);

    $dryRun = (bool) (getenv('MAIL_STRESS_DRY_RUN') ?: $_ENV['MAIL_STRESS_DRY_RUN'] ?? false);

    if ($dryRun) {
        echo PHP_EOL . '=== MailStressTest — DRY RUN ===' . PHP_EOL;
        echo 'Mailer: test_webmail' . PHP_EOL;
        echo 'Host: ' . Config::get('mail.mailers.test_webmail.host') . PHP_EOL;
        echo 'Puerto: ' . Config::get('mail.mailers.test_webmail.port') . PHP_EOL;
        echo 'Usuario: ' . Config::get('mail.mailers.test_webmail.username') . PHP_EOL;
        echo 'Destinatarios: ' . count($this->destinatarios) . ' (aliases +test1..+test15)' . PHP_EOL;
        echo PHP_EOL . 'Para enviar realmente, correr sin MAIL_STRESS_DRY_RUN:' . PHP_EOL;
        echo '  php artisan test --filter MailStressTest' . PHP_EOL;

        // Verificar que el mailer existe y tiene las credenciales
        expect(Config::get('mail.mailers.test_webmail.host'))->not->toBeNull();
        expect(Config::get('mail.mailers.test_webmail.username'))->not->toBeNull();

        return;
    }

    echo PHP_EOL . '============================================================' . PHP_EOL;
    echo '  STRESS TEST — Envío incremental con mailer real' . PHP_EOL;
    echo '  Mailer: test_webmail (' . Config::get('mail.mailers.test_webmail.host') . ')' . PHP_EOL;
    echo '  Destinatarios: ' . count($this->destinatarios) . PHP_EOL;
    echo '============================================================' . PHP_EOL . PHP_EOL;

    // 1. Generar PDF base (~50-100 KB con DomPDF)
    echo '[1/4] Generando PDF base con DomPDF... ';
    $inicioPdf = microtime(true);
    $pdfBase = generarPdfBase();
    $tiempoPdf = round(microtime(true) - $inicioPdf, 2);
    $tamañoBase = strlen($pdfBase);
    echo "OK ($tamañoBase bytes / " . round($tamañoBase / 1024 / 1024, 2) . " MB en {$tiempoPdf}s)" . PHP_EOL;

    // 2. Tamaños incrementales: base, +2MB, +4MB, ..., hasta +18MB
    //    Total: ~0.05, 2, 4, 6, 8, 10, 12, 14, 16, 18 MB
    //    10 tandas × 15 destinatarios = 150 envíos (vs 225 con 15 tandas)
    $tamanos = [];
    for ($i = 0; $i <= 9; $i++) {
        $tamanos[] = $tamañoBase + ($i * 2 * 1024 * 1024); // base + i*2MB
    }

    echo PHP_EOL . '[2/4] Enviando ' . count($tamanos) . ' tandas de ' . count($this->destinatarios) . ' destinatarios cada una...' . PHP_EOL;
    echo str_repeat('-', 70) . PHP_EOL;
    printf("%-6s | %-10s | %-8s | %-8s | %s\n", 'Tanda', 'Tamaño', 'Enviados', 'Fallidos', 'SMTP / Error');
    echo str_repeat('-', 70) . PHP_EOL;

    $resultados = [];
    $primeroFallido = null;

    foreach ($tamanos as $indice => $tamañoObjetivo) {
        // Padded PDF del tamaño objetivo
        $pdf = paddedPdf($pdfBase, $tamañoObjetivo);
        $tamañoReal = strlen($pdf);

        echo PHP_EOL . "Tanda " . ($indice + 1) . " (PDF " . round($tamañoReal / 1024 / 1024, 2) . " MB):" . PHP_EOL;

        $enviados = 0;
        $fallidos = 0;
        $ultimosCodigos = [];

        foreach ($this->destinatarios as $destino) {
            try {
                // Crear el mailable con el PDF padded
                $mailable = new GuardiaNovedadesMail(
                    guardia: $this->guardia,
                    remitenteName: 'Capitán Test',
                    incluirAdjuntos: false,
                    pdfContent: $pdf,
                    enviarZip: false,
                    zipContent: null,
                );

                // Enviar EXPLÍCITAMENTE con el mailer test_webmail
                Mail::mailer('test_webmail')->to($destino)->send($mailable);
                $enviados++;
            } catch (\Throwable $e) {
                $fallidos++;
                $codigo = extraerCodigoSmtp($e);
                $clasificacion = clasificarFalloSmtp($codigo, $e->getMessage());
                $ultimosCodigos[] = $codigo ?? 'N/A';

                // Si el fallo es consistente (no es un timeout aleatorio),
                // marcar como primero fallido si aún no hay
                if ($primeroFallido === null && $codigo !== '421') {
                    $primeroFallido = [
                        'tamaño' => $tamañoReal,
                        'destino' => $destino,
                        'codigo' => $codigo,
                        'clasificacion' => $clasificacion,
                        'mensaje' => $e->getMessage(),
                        'tipo_excepcion' => class_basename($e),
                    ];
                }
            }
        }

        // Mostrar fila del resumen
        $tamañoMb = round($tamañoReal / 1024 / 1024, 2);
        printf("%-6d | %-10s | %-8d | %-8d | %s\n",
            $indice + 1,
            $tamañoMb . ' MB',
            $enviados,
            $fallidos,
            implode(', ', array_unique($ultimosCodigos)) ?: 'OK'
        );

        $resultados[] = [
            'tamaño_bytes' => $tamañoReal,
            'tamaño_mb' => $tamañoMb,
            'enviados' => $enviados,
            'fallidos' => $fallidos,
            'codigos' => array_unique($ultimosCodigos),
        ];

        // Si la tanda completa falló para todos los destinatarios,
        // el límite está entre esta tanda y la anterior
        if ($fallidos === count($this->destinatarios) && $indice > 0) {
            echo PHP_EOL . '⚠️  Tanda completa fallida — límite confirmado entre '
                . $resultados[$indice - 1]['tamaño_mb'] . ' MB y ' . $tamañoMb . ' MB' . PHP_EOL;
            break;
        }
    }

    // 3. Reporte final
    echo PHP_EOL . str_repeat('=', 70) . PHP_EOL;
    echo '  REPORTE FINAL' . PHP_EOL;
    echo str_repeat('=', 70) . PHP_EOL;

    echo PHP_EOL . 'Resumen por tanda:' . PHP_EOL;
    foreach ($resultados as $r) {
        $estado = $r['fallidos'] === 0 ? '✅'
            : ($r['enviados'] === 0 ? '❌' : '⚠️');
        echo sprintf('  %s Tanda %d: %s | Enviados: %d | Fallidos: %d | SMTP: %s',
            $estado,
            array_search($r, $resultados, true) + 1,
            $r['tamaño_mb'] . ' MB',
            $r['enviados'],
            $r['fallidos'],
            implode(', ', $r['codigos']) ?: 'OK'
        ) . PHP_EOL;
    }

    if ($primeroFallido) {
        echo PHP_EOL . '🎯 PRIMER FALLO DETECTADO:' . PHP_EOL;
        echo '  Tamaño del PDF: ' . round($primeroFallido['tamaño'] / 1024 / 1024, 2) . ' MB' . PHP_EOL;
        echo '  Destino: ' . $primeroFallido['destino'] . PHP_EOL;
        echo '  Código SMTP: ' . ($primeroFallido['codigo'] ?? 'Ninguno') . PHP_EOL;
        echo '  Clasificación: ' . $primeroFallido['clasificacion'] . PHP_EOL;
        echo '  Tipo de excepción: ' . $primeroFallido['tipo_excepcion'] . PHP_EOL;
        echo '  Mensaje: ' . Str::limit($primeroFallido['mensaje'], 200) . PHP_EOL;

        // Interpretar el resultado
        if ($primeroFallido['codigo'] === '552') {
            echo PHP_EOL . '  👉 El servidor Zimbra rechaza explícitamente por tamaño máximo (552).' . PHP_EOL;
            echo '     El límite está entre ' . round(($primeroFallido['tamaño'] - 2 * 1024 * 1024) / 1024 / 1024, 2)
                . ' MB y ' . round($primeroFallido['tamaño'] / 1024 / 1024, 2) . ' MB.' . PHP_EOL;
        } elseif ($primeroFallido['codigo'] === '550') {
            echo PHP_EOL . '  👉 El servidor rechaza el destinatario o la retransmisión (550).' . PHP_EOL;
            echo '     Podría ser un límite de remitente o restricción de dominio.' . PHP_EOL;
        } elseif ($primeroFallido['codigo'] === null) {
            echo PHP_EOL . '  👉 Sin código SMTP — la conexión se cortó (timeout/EOF).' . PHP_EOL;
            echo '     El servidor podría estar cerrando la conexión por tamaño o tiempo.' . PHP_EOL;
        }
    } else {
        echo PHP_EOL . '  ✅ Todos los correos se enviaron exitosamente.' . PHP_EOL;
        echo '     No se alcanzó el límite en los tamaños probados.' . PHP_EOL;
    }

    echo PHP_EOL . 'Destinatarios de prueba (revisar bandeja de s1bcom1@ejercito.mil.uy):' . PHP_EOL;
    echo '  s1bcom1+test1@ejercito.mil.uy' . PHP_EOL;
    echo '  s1bcom1+test2@ejercito.mil.uy' . PHP_EOL;
    echo '  ... hasta s1bcom1+test15@ejercito.mil.uy' . PHP_EOL;
    echo PHP_EOL . 'Para borrar los correos de prueba, filtrar en el webmail:' . PHP_EOL;
    echo '  Buscador: "Novedades de guardia del 19/08/2026"' . PHP_EOL;
    echo '  Remitente: s1bcom1@ejercito.mil.uy' . PHP_EOL;

    echo PHP_EOL . str_repeat('=', 70) . PHP_EOL . PHP_EOL;

    // Assertions mínimas para que el test "pase" incluso si hay fallos SMTP
    // (los fallos son esperados — lo importante es el reporte)
    expect($this->destinatarios)->toHaveCount(15);
    expect($resultados)->not->toBeEmpty();
}); // Ajustar max_execution_time en php.ini si es necesario (1 hora recomendado)
