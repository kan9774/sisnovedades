<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

class ProcesarRebotesCommand extends Command
{
    protected $signature = 'mail:procesar-rebotes';

    protected $description = 'Lee la bandeja por IMAP, detecta rebotes (DSN) y los correlaciona con guardia_correos_enviados para registrarlos en guardia_correos_fallidos';

    /**
     * Carpeta IMAP que se revisa. Se usa como parte de la clave de
     * deduplicación en imap_mensajes_procesados (el UID de IMAP es único
     * por carpeta, no globalmente).
     */
    private const FOLDER = 'INBOX';

    /**
     * Ventana de días hacia atrás que se vuelve a escanear en cada corrida.
     * No hace falta traer todo el historial del buzón: alcanza con cubrir
     * unos días por si el comando estuvo caído, y la tabla
     * imap_mensajes_procesados se encarga de no reprocesar lo ya visto.
     */
    private const DIAS_VENTANA = 7;

    public function handle(): int
    {
        $this->info('Conectando a IMAP...');

        try {
            $clientManager = new ClientManager();

            $client = $clientManager->make([
                'host'          => config('mail.bounce_imap.host'),
                'port'          => config('mail.bounce_imap.port'),
                'encryption'    => config('mail.bounce_imap.encryption'),
                'validate_cert' => true,
                'username'      => config('mail.bounce_imap.username'),
                'password'      => config('mail.bounce_imap.password'),
                'protocol'      => 'imap',
            ]);

            $client->connect();
        } catch (\Throwable $e) {
            Log::error('ProcesarRebotesCommand: fallo al conectar por IMAP - ' . $e->getMessage());
            $this->error('No se pudo conectar por IMAP: ' . $e->getMessage());
            return self::FAILURE;
        }

        $folder = $client->getFolder(self::FOLDER);

        // Ya NO filtramos por unseen(): el flag Seen/Unseen de IMAP puede
        // ser modificado por cualquiera que abra el buzón en Zimbra
        // (webmail), lo que hacía que el comando saltara rebotes reales
        // sin registrar nada. La deduplicación ahora es propia, contra
        // imap_mensajes_procesados (folder + uid), inmune a que alguien
        // abra el correo manualmente.
        $mensajes = $folder->messages()
            ->since(now()->subDays(self::DIAS_VENTANA))
            ->get();

        $uidsYaProcesados = DB::table('imap_mensajes_procesados')
            ->where('folder', self::FOLDER)
            ->pluck('uid')
            ->flip();

        $this->info("Mensajes en ventana de {$this->getDiasVentanaLabel()}: {$mensajes->count()}");

        $rebotesProcesados = 0;
        $rebotesSinCorrelacion = 0;
        $rebotesSinEnvioCorrelacionado = 0;

        foreach ($mensajes as $mensaje) {
            $uid = $mensaje->getUid();

            if ($uidsYaProcesados->has($uid)) {
                continue;
            }

            if (!$this->pareceRebote($mensaje)) {
                // No es un rebote (correo normal de la casilla). No lo
                // registramos en imap_mensajes_procesados: es barato
                // volver a evaluarle el asunto/remitente en la próxima
                // corrida y así la tabla no crece con mail que nunca
                // nos interesó.
                continue;
            }

            $datos = $this->parsearDsn($mensaje);

            if (!$datos || !$datos['message_id_original']) {
                $rebotesSinCorrelacion++;
                Log::warning('ProcesarRebotesCommand: rebote sin datos suficientes para correlacionar', [
                    'subject' => $mensaje->getSubject(),
                    'uid'     => $uid,
                ]);
                $this->marcarProcesado($uid, 'sin_correlacion');
                continue;
            }

            $envioOriginal = DB::table('guardia_correos_enviados')
                ->where('message_id', $datos['message_id_original'])
                ->first();

            if (!$envioOriginal) {
                $rebotesSinEnvioCorrelacionado++;
                Log::info('ProcesarRebotesCommand: rebote sin envío correlacionado en guardia_correos_enviados', [
                    'destinatario'        => $datos['destinatario'] ?? null,
                    'message_id_parseado' => $datos['message_id_original'],
                    'uid'                 => $uid,
                ]);
                $this->marcarProcesado($uid, 'sin_envio_relacionado');
                continue;
            }

            DB::table('guardia_correos_fallidos')->insert([
                'guardia_id' => $envioOriginal->guardia_id,
                'user_id'    => $envioOriginal->user_id,
                'email'      => $datos['destinatario'] ?? $envioOriginal->email,
                'motivo'     => $this->clasificarMotivo($datos['status'], $datos['diagnostico']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('guardia_correos_enviados')
                ->where('id', $envioOriginal->id)
                ->update(['rebotado_en' => now()]);

            $this->marcarProcesado($uid, 'procesado');
            $rebotesProcesados++;

            $this->info("Rebote registrado: {$datos['destinatario']} (guardia #{$envioOriginal->guardia_id})");
        }

        $this->info("Listo. Rebotes procesados: {$rebotesProcesados}. Sin correlación (revisar a mano): {$rebotesSinCorrelacion}. Sin envío correlacionado (ver logs): {$rebotesSinEnvioCorrelacionado}.");

        return self::SUCCESS;
    }

    private function getDiasVentanaLabel(): string
    {
        return self::DIAS_VENTANA . ' día' . (self::DIAS_VENTANA === 1 ? '' : 's');
    }

    /**
     * Registra el UID como procesado en imap_mensajes_procesados. Usa
     * upsert para que sea idempotente: si dos corridas se solaparan (no
     * debería, pero por las dudas) no rompe por violar el índice único.
     */
    private function marcarProcesado(int $uid, string $resultado): void
    {
        DB::table('imap_mensajes_procesados')->upsert(
            [[
                'folder'       => self::FOLDER,
                'uid'          => $uid,
                'resultado'    => $resultado,
                'procesado_en' => now(),
            ]],
            ['folder', 'uid'],
            ['resultado', 'procesado_en'],
        );
    }

    /**
     * Heurística para identificar si un mensaje es un DSN (Delivery Status
     * Notification) de rebote, sin depender de parsear el body todavía.
     */
    private function pareceRebote(Message $mensaje): bool
    {
        $remitentes = collect($mensaje->getFrom())
            ->map(fn ($direccion) => strtolower($direccion->mail ?? ''))
            ->implode(' ');

        if (str_contains($remitentes, 'mailer-daemon') || str_contains($remitentes, 'postmaster')) {
            return true;
        }

        $asunto = strtolower($mensaje->getSubject() ?? '');

        return str_contains($asunto, 'delivery status notification')
            || str_contains($asunto, 'delivery has failed')
            || str_contains($asunto, 'mail delivery failed')
            || str_contains($asunto, 'undelivered mail')
            || str_contains($asunto, 'returned mail');
    }

    /**
     * Parsea el cuerpo crudo del DSN con regex, en vez de depender de la
     * clasificación exacta de "attachments" que hace la librería para las
     * partes multipart/report (message/delivery-status). El formato de un
     * DSN está estandarizado por RFC 3464, así que estos campos vienen
     * siempre en texto plano sin codificar dentro de esa parte.
     */
    private function parsearDsn(Message $mensaje): ?array
    {
        $raw = $mensaje->getRawBody();

        if (!$raw || !preg_match('/Final-Recipient:\s*rfc822;\s*([^\r\n]+)/i', $raw, $mDestinatario)) {
            return null;
        }

        $destinatario = trim($mDestinatario[1]);

        $status = null;
        if (preg_match('/^Status:\s*([\d.]+)/im', $raw, $mStatus)) {
            $status = trim($mStatus[1]);
        }

        $diagnostico = null;
        if (preg_match('/Diagnostic-Code:\s*([^\r\n]+)/i', $raw, $mDiagnostico)) {
            $diagnostico = trim($mDiagnostico[1]);
        }

        // El DSN suele incluir los headers del mensaje original (rfc822)
        // más abajo en el cuerpo. Ahí es donde aparece el Message-ID real
        // que generamos al enviar. Tomamos el ÚLTIMO match porque el
        // primer "Message-ID" que aparece a veces es el del propio
        // reporte de rebote, no el del mensaje original.
        $messageIdOriginal = null;
        if (preg_match_all('/Message-ID:\s*<([^>]+)>/i', $raw, $mMessageId) && count($mMessageId[1]) > 0) {
            $messageIdOriginal = end($mMessageId[1]);
        }

        return [
            'destinatario'          => $destinatario,
            'status'                => $status,
            'diagnostico'           => $diagnostico,
            'message_id_original'   => $messageIdOriginal,
        ];
    }

    private function clasificarMotivo(?string $status, ?string $diagnostico): string
    {
        $texto = strtolower(($status ?? '') . ' ' . ($diagnostico ?? ''));

        if ($texto === '' || $texto === ' ') {
            return '❓ Rebote sin detalle (revisar manualmente)';
        }

        if (str_contains($texto, '5.1.1') || str_contains($texto, 'user unknown') || str_contains($texto, 'no such user')) {
            return '❌ Dirección inexistente';
        }

        if (str_contains($texto, '5.2.2') || str_contains($texto, 'mailbox full') || str_contains($texto, 'quota exceeded') || str_contains($texto, 'over quota')) {
            return '⚠️ Casilla llena (quota excedida)';
        }

        if (str_contains($texto, '5.7') || str_contains($texto, 'spam') || str_contains($texto, 'blocked') || str_contains($texto, 'rejected')) {
            return '🚫 Bloqueado / marcado como spam por el destinatario';
        }

        if (str_contains($texto, '4.') ) {
            return '⏳ Rebote temporal (4.x) - reintentar más tarde';
        }

        return '❓ Rebote (' . trim(($status ?? '') . ' ' . ($diagnostico ?? '')) . ')';
    }
}