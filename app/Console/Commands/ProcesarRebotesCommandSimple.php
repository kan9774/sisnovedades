<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcesarRebotesCommandSimple extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:procesar-rebotes-simple';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa los mensajes de rebote de correo para registrar errores en la base de datos (versión simple de prueba)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Comando de prueba de procesamiento de rebotes ejecutado correctamente');
        
        // Simulamos el proceso básico
        $this->info('Verificando conexión a base de datos...');
        
        try {
            DB::connection()->getPdo();
            $this->info('Conexión a base de datos exitosa');
        } catch (\Exception $e) {
            $this->error('Error en conexión a base de datos: ' . $e->getMessage());
            return 1;
        }
        
        // Verificamos que exista la tabla
        try {
            $exists = DB::select('SHOW TABLES LIKE "guardia_correos_enviados"');
            if (count($exists) > 0) {
                $this->info('Tabla guardia_correos_enviados existe');
            } else {
                $this->warn('Tabla guardia_correos_enviados no existe');
            }
        } catch (\Exception $e) {
            $this->warn('Error al verificar tabla guardia_correos_enviados: ' . $e->getMessage());
        }
        
        try {
            $exists = DB::select('SHOW TABLES LIKE "guardia_correos_fallidos"');
            if (count($exists) > 0) {
                $this->info('Tabla guardia_correos_fallidos existe');
            } else {
                $this->warn('Tabla guardia_correos_fallidos no existe');
            }
        } catch (\Exception $e) {
            $this->warn('Error al verificar tabla guardia_correos_fallidos: ' . $e->getMessage());
        }
        
        return 0;
    }
}