<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imap_mensajes_procesados', function (Blueprint $table) {
            $table->id();

            // Carpeta IMAP (normalmente 'INBOX') + UID del mensaje dentro de esa
            // carpeta. El UID de IMAP es único por carpeta, no globalmente, así
            // que la combinación de ambos es la clave real de deduplicación.
            $table->string('folder');
            $table->unsignedBigInteger('uid');

            // Resultado del procesamiento, útil para diagnóstico sin tener que
            // ir a los logs: si se pudo correlacionar con un envío nuestro o no.
            $table->enum('resultado', [
                'procesado',            // rebote correlacionado, insertado en guardia_correos_fallidos
                'sin_correlacion',      // parecía rebote pero no se pudo extraer message_id
                'sin_envio_relacionado', // rebote válido pero no corresponde a un envío nuestro
                'no_es_rebote',         // mensaje descartado por pareceRebote()
            ]);

            $table->timestamp('procesado_en');

            $table->unique(['folder', 'uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imap_mensajes_procesados');
    }
};