<?php

namespace App\Console\Commands;

use App\Mail\GuardiaNovedadesMail;
use App\Models\Guard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailStressTest extends Command
{
    protected $signature = 'mail:stress-test
                            {--tandas=10 : Cantidad de tandas incrementales}
                            {--destinatarios=3 : Cantidad de destinatarios por tanda (1-15)}
                            {--incremento=2 : Incremento en MB por tanda (soporta decimales: 0.5, 0.25)}
                            {--desde-mb=0 : Tamaño inicial en MB (para continuar una corrida previa)}
                            {--sanity : Solo enviar 1 correo de prueba}';

    protected $description = 'Stress test: enviar correos con PDF de tamaño incremental contra mailer real test_webmail';

    public function handle(): int
    {
        // Aumentar memory_limit: codificar PDFs grandes en Base64 (×3) requiere
        // ~240MB para un PDF de 80MB. Con overhead de Laravel + SMTP + DomPDF,
        // 1024MB no alcanza para tandas grandes. Subo a 2GB.
        ini_set('memory_limit', '2048M');

        // Configurar mailer test_webmail
        Config::set('mail.mailers.test_webmail', [
            'transport'  => 'smtp',
            'host'       => 'webmail.ejercito.mil.uy',
            'port'       => 587,
            'encryption' => 'tls',
            'username'   => 's1bcom1@ejercito.mil.uy',
            'password'   => 'HPDNTCKGJXPDCLKI',
            'timeout'    => null,
        ]);

        if ($this->option('sanity')) {
            return $this->runSanityCheck();
        }

        $tandas = (int) $this->option('tandas');
        $destinatarios = (int) $this->option('destinatarios');
        $incremento = (float) $this->option('incremento');
        $desdeMb = (float) $this->option('desde-mb');

        // Validar
        if ($destinatarios < 1 || $destinatarios > 15) {
            $this->error('Cantidad de destinatarios debe ser entre 1 y 15.');

            return static::INVALID;
        }

        if ($tandas < 1 || $tandas > 20) {
            $this->error('Cantidad de tandas debe ser entre 1 y 20.');

            return static::INVALID;
        }

        // Crear guardia de prueba (fecha futura aleatoria amplia para evitar unique constraint)
        $this->info('Creando guardia de prueba...');
        $guardia = Guard::create([
            'date'     => now()->addDays(rand(100, 730))->format('Y-m-d'),
            'status'   => 'closed',
            'notes'    => 'Stress test mailer - ' . now()->format('Y-m-d H:i:s'),
            'captain_id' => 1,
            'oficer_id'  => 1,
        ]);

        // Generar PDF base
        $this->info('Generando PDF base con DomPDF...');
        $pdfBase = $this->generarPdfBase();
        $tamañoBase = strlen($pdfBase);

        $this->newLine();
        $this->info("PDF base: {$tamañoBase} bytes (" . round($tamañoBase / 1024, 2) . " KB)");
        $this->info("Mailer: test_webmail (webmail.ejercito.mil.uy:587)");
        $this->info("Tandas: {$tandas} | Destinatarios por tanda: {$destinatarios}");
        $this->info("Incremento: {$incremento} MB por tanda");
        $this->newLine();

        $this->line(str_repeat('=', 80));
        $this->info('  STRESS TEST — Envío incremental con mailer real');
        $this->line(str_repeat('=', 80));
        $this->newLine();

        printf("%-6s | %-12s | %-8s | %-8s | %s\n",
            'Tanda', 'Tamaño PDF', 'Enviados', 'Fallidos', 'SMTP / Error'
        );
        $this->line(str_repeat('-', 80));

        $resultados = [];
        $primeroFallido = null;
        $totalEnviados = 0;
        $totalFallidos = 0;

        for ($i = 0; $i < $tandas; $i++) {
            $offsetMb = $desdeMb + ($i * $incremento);
            $tamañoObjetivo = $tamañoBase + ($offsetMb * 1024 * 1024);
            $pdf = $this->paddedPdf($pdfBase, $tamañoObjetivo);
            $tamañoReal = strlen($pdf);
            $tamañoMb = round($tamañoReal / 1024 / 1024, 2);

            $this->newLine();
            $this->info("Tanda " . ($i + 1) . " (PDF {$tamañoMb} MB)...");

            $enviados = 0;
            $fallidos = 0;
            $codigos = [];

            for ($d = 1; $d <= $destinatarios; $d++) {
                $destino = "s1bcom1+test{$i}_{$d}@ejercito.mil.uy";

                try {
                    $mailable = new GuardiaNovedadesMail(
                        guardia: $guardia,
                        remitenteName: 'Capitán Test',
                        incluirAdjuntos: false,
                        pdfContent: $pdf,
                        enviarZip: false,
                        zipContent: null,
                    );

                    Mail::mailer('test_webmail')->to($destino)->send($mailable);
                    $enviados++;
                    $totalEnviados++;
                } catch (\Throwable $e) {
                    $fallidos++;
                    $totalFallidos++;
                    $codigo = $this->extraerCodigoSmtp($e);
                    $codigos[] = $codigo ?? 'N/A';

                    if ($primeroFallido === null && $codigo !== '421') {
                        $primeroFallido = [
                            'tamaño' => $tamañoReal,
                            'tamaño_mb' => $tamañoMb,
                            'destino' => $destino,
                            'codigo' => $codigo,
                            'clasificacion' => $this->clasificarFalloSmtp($codigo, $e->getMessage()),
                            'mensaje' => $e->getMessage(),
                            'tipo_excepcion' => class_basename($e),
                            'tanda' => $i + 1,
                        ];
                    }
                }
            }

            printf("%-6d | %-12s | %-8d | %-8d | %s\n",
                $i + 1,
                $tamañoMb . ' MB',
                $enviados,
                $fallidos,
                implode(', ', array_unique($codigos)) ?: 'OK'
            );

            $resultados[] = [
                'tanda' => $i + 1,
                'tamaño_mb' => $tamañoMb,
                'enviados' => $enviados,
                'fallidos' => $fallidos,
                'codigos' => array_unique($codigos),
            ];

            // Si la tanda completa falló, el límite está entre esta y la anterior
            if ($fallidos === $destinatarios && $i > 0) {
                $this->newLine();
                $this->warn("⚠️  Tanda completa fallida — límite confirmado entre {$resultados[$i - 1]['tamaño_mb']} MB y {$tamañoMb} MB");
                break;
            }
        }

        // Reporte final
        $this->newLine();
        $this->line(str_repeat('=', 80));
        $this->info('  REPORTE FINAL');
        $this->line(str_repeat('=', 80));
        $this->newLine();

        $this->info("Total enviados: {$totalEnviados}");
        $this->info("Total fallidos: {$totalFallidos}");
        $this->newLine();

        if ($primeroFallido) {
            $this->warn('🎯 PRIMER FALLO DETECTADO:');
            $this->line("  Tamaño del PDF: {$primeroFallido['tamaño_mb']} MB");
            $this->line("  Destino: {$primeroFallido['destino']}");
            $this->line("  Código SMTP: " . ($primeroFallido['codigo'] ?? 'Ninguno'));
            $this->line("  Clasificación: {$primeroFallido['clasificacion']}");
            $this->line("  Tipo de excepción: {$primeroFallido['tipo_excepcion']}");
            $this->line("  Mensaje: " . \Illuminate\Support\Str::limit($primeroFallido['mensaje'], 200));
            $this->newLine();

            if ($primeroFallido['codigo'] === '552') {
                $this->info("👉 El servidor Zimbra rechaza explícitamente por tamaño máximo (552).");
                $antes = $primeroFallido['tamaño'] - ($incremento * 1024 * 1024);
                $this->info("   El límite está entre " . round($antes / 1024 / 1024, 2) . " MB y {$primeroFallido['tamaño_mb']} MB.");
            } elseif ($primeroFallido['codigo'] === '550') {
                $this->info("👉 El servidor rechaza el destinatario o la retransmisión (550).");
                $this->info("   Podría ser un límite de remitente o restricción de dominio.");
            } elseif ($primeroFallido['codigo'] === null) {
                $this->info("👉 Sin código SMTP — la conexión se cortó (timeout/EOF).");
                $this->info("   El servidor podría estar cerrando la conexión por tamaño o tiempo.");
            }
        } else {
            $this->info("✅ Todos los correos se enviaron exitosamente.");
            $this->info("   No se alcanzó el límite en los tamaños probados.");
        }

        // Destinatarios de prueba
        $this->newLine();
        $this->info('Destinatarios de prueba (revisar bandeja de s1bcom1@ejercito.mil.uy):');
        for ($i = 0; $i < count($resultados); $i++) {
            for ($d = 1; $d <= $destinatarios; $d++) {
                $this->line("  s1bcom1+test{$i}_{$d}@ejercito.mil.uy");
            }
        }
        $this->newLine();
        $this->info('Para borrar los correos de prueba, filtrar en el webmail:');
        $this->line('  Buscador: "Novedades de guardia del ' . $guardia->date->format('d/m/Y') . '"');
        $this->line('  Remitente: s1bcom1@ejercito.mil.uy');

        // Limpiar guardia de prueba
        $guardia->delete();
        $this->newLine();
        $this->info('Guardia de prueba eliminada.');

        $this->line(str_repeat('=', 80));

        return static::SUCCESS;
    }

    /**
     * Sanity check: 1 correo con PDF base.
     */
    protected function runSanityCheck(): int
    {
        $this->info('=== Sanity Check ===');
        $this->info('Mailer: test_webmail (webmail.ejercito.mil.uy:587)');
        $this->info('Destinatario: s1bcom1+sanity@ejercito.mil.uy');
        $this->newLine();

        // Crear guardia (fecha futura aleatoria amplia para evitar unique constraint)
        $guardia = Guard::create([
            'date'     => now()->addDays(rand(100, 730))->format('Y-m-d'),
            'status'   => 'closed',
            'notes'    => 'Sanity check mailer',
            'captain_id' => 1,
            'oficer_id'  => 1,
        ]);

        // Generar PDF base
        $this->info('Generando PDF base...');
        $pdfBase = $this->generarPdfBase();
        $this->info("PDF: " . strlen($pdfBase) . " bytes (" . round(strlen($pdfBase) / 1024, 2) . " KB)");

        // Enviar
        $this->info('Enviando correo...');
        try {
            $mailable = new GuardiaNovedadesMail(
                guardia: $guardia,
                remitenteName: 'Capitán Test',
                incluirAdjuntos: false,
                pdfContent: $pdfBase,
                enviarZip: false,
                zipContent: null,
            );

            Mail::mailer('test_webmail')->to('s1bcom1+sanity@ejercito.mil.uy')->send($mailable);

            $this->newLine();
            $this->info('✅ Correo enviado exitosamente a s1bcom1+sanity@ejercito.mil.uy');
            $this->info('   Revisar la bandeja de entrada.');
            $this->newLine();
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('❌ Error: ' . $e->getMessage());
            $codigo = $this->extraerCodigoSmtp($e);
            if ($codigo) {
                $this->error("   Código SMTP: $codigo");
            }
            $this->line('');

            $guardia->delete();

            return static::FAILURE;
        }

        $guardia->delete();
        $this->info('Guardia de prueba eliminada.');

        return static::SUCCESS;
    }

    /**
     * Genera un PDF base pequeño con DomPDF (~50-100 KB).
     */
    protected function generarPdfBase(): string
    {
        $dompdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<html><body></body></html>');

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
     * Padded PDF: agrega bytes al final para alcanzar el tamaño objetivo.
     */
    protected function paddedPdf(string $pdfBase, int $tamañoObjetivo): string
    {
        $tamañoActual = strlen($pdfBase);

        if ($tamañoActual >= $tamañoObjetivo) {
            return $pdfBase;
        }

        $bytesFaltantes = $tamañoObjetivo - $tamañoActual;
        $padding = '';
        while (strlen($padding) < $bytesFaltantes) {
            $padding .= random_bytes(min(1024, $bytesFaltantes - strlen($padding)));
        }

        return $pdfBase . substr($padding, 0, $bytesFaltantes);
    }

    /**
     * Extrae el código de respuesta SMTP del mensaje de una excepción.
     */
    protected function extraerCodigoSmtp(\Throwable $e): ?string
    {
        if (preg_match('/\b([45]\d{2})\b/', $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Clasifica el tipo de fallo según el código SMTP.
     */
    protected function clasificarFalloSmtp(?string $codigo, string $mensaje): string
    {
        if ($codigo === null) {
            return 'Sin código SMTP — ' . \Illuminate\Support\Str::limit($mensaje, 100);
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
}
