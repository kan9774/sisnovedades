<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RestoreDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $backupPath;
    public string $operationId;
    public ?string $safetyBackupPath = null;
    public string $status = 'starting';
    public string $logFile;
    public ?string $errorDetails = null;

    public function __construct(string $backupPath, string $operationId)
    {
        $this->backupPath = $backupPath;
        $this->operationId = $operationId;
        $this->logFile = 'restore-' . $operationId . '.log';
    }

    public function timeout(): int
    {
        return 3600;
    }

    public function handle(): void
    {
        $disk = Storage::disk('backup');

        if (!$disk->exists($this->backupPath)) {
            $this->fail(new \RuntimeException('Backup no encontrado: ' . $this->backupPath));
            return;
        }

        $this->log('=== INICIO RESTAURACIÓN ===');
        $this->log('Backup origen: ' . $this->backupPath);

        // Paso 1: Backup de seguridad pre-restore
        $this->status = 'backing_up_safety';
        $this->updateStatus();
        $this->log('Creando backup de seguridad pre-restore...');

        $safetyTimestamp = now()->format('Y-m-d-H-i-s');
        $safetyFilename = 'pre-restore-safety-' . $safetyTimestamp;

        try {
            Artisan::call('backup:run', [
                '--only-db' => true,
                '--filename' => $safetyFilename,
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();
            $this->log('Backup seguridad output: ' . trim($output));

            // Buscar el archivo de seguridad en storage
            $safetyPath = null;
            $currentDriver = config('database.default', 'mysql');
            $appName = config('app.name', 'novedades');
            $backupNamePattern = $appName . '-' . $currentDriver;

            $safetyFiles = $disk->allFiles();
            foreach ($safetyFiles as $file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                if (strpos($filename, 'pre-restore-safety-') === 0) {
                    $safetyPath = $file;
                    break;
                }
            }

            if (!$safetyPath) {
                throw new \RuntimeException('No se encontró el backup de seguridad generado');
            }

            $this->safetyBackupPath = $safetyPath;
            $this->log('Backup de seguridad creado: ' . $safetyPath);
        } catch (\Exception $e) {
            $this->log('ERROR al crear backup de seguridad: ' . $e->getMessage());
            $this->status = 'failed';
            $this->errorDetails = 'No se pudo crear backup de seguridad: ' . $e->getMessage() . '. Abortando restauración.';
            $this->updateStatus();
            $this->fail($e);
            return;
        }

        // Paso 2: Activar modo mantenimiento
        $this->status = 'restoring';
        $this->updateStatus();
        $this->log('Activando modo mantenimiento...');

        try {
            Artisan::call('down', ['--secret' => 'restore-mode']);
            $this->log('Modo mantenimiento activado.');
        } catch (\Exception $e) {
            $this->log('Advertencia al activar modo mantenimiento: ' . $e->getMessage());
        }

        // Paso 3: Extraer backup
        $this->log('Extrayendo archivo de backup...');

        $tempDir = storage_path('app/restore-temp-' . $this->operationId);
        @mkdir($tempDir, 0755, true);

        try {
            $zipPath = $disk->path($this->backupPath);
            $zip = new \ZipArchive;

            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('No se pudo abrir el archivo de backup: ' . $this->backupPath);
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $this->log('Backup extraído exitosamente.');
        } catch (\Exception $e) {
            $this->log('ERROR al extraer backup: ' . $e->getMessage());
            $this->cleanupTemp($tempDir);
            $this->status = 'failed';
            $this->errorDetails = 'Error al extraer backup: ' . $e->getMessage();
            $this->updateStatus();
            $this->fail($e);
            return;
        }

        // Paso 4: Localizar archivo SQL
        $this->log('Buscando archivo SQL dentro del backup...');

        $sqlFile = $this->findSqlFile($tempDir);

        if (!$sqlFile) {
            $this->log('ERROR: No se encontró archivo SQL en el backup.');
            $this->cleanupTemp($tempDir);
            $this->status = 'failed';
            $this->errorDetails = 'No se encontró archivo .sql en el backup. Estructura inesperada.';
            $this->updateStatus();
            $this->fail(new \RuntimeException('No se encontró archivo SQL en el backup'));
            return;
        }

        $this->log('Archivo SQL localizado: ' . basename($sqlFile));

        // Paso 5: Ejecutar restore según driver
        $driver = config('database.default', 'mysql');
        $this->log('Motor de base de datos: ' . $driver);

        $restoreSuccess = false;

        if ($driver === 'mysql') {
            $restoreSuccess = $this->restoreMysql($sqlFile);
        } elseif ($driver === 'pgsql') {
            $restoreSuccess = $this->restorePgsql($sqlFile);
        } else {
            $this->log('ERROR: Motor no soportado para restore: ' . $driver);
            $this->cleanupTemp($tempDir);
            $this->status = 'failed';
            $this->errorDetails = 'Motor de base de datos no soportado para restauración: ' . $driver;
            $this->updateStatus();
            $this->fail(new \RuntimeException('Motor no soportado: ' . $driver));
            return;
        }

        // Paso 6: Limpiar y finalizar
        $this->cleanupTemp($tempDir);

        if ($restoreSuccess) {
            $this->log('=== RESTAURACIÓN EXITOSA ===');
            $this->status = 'completed';
            $this->updateStatus();
        } else {
            $this->log('=== RESTAURACIÓN FALLIDA ===');
            $this->status = 'failed';
            $this->errorDetails = 'El comando de importación falló. Revisar logs detallados en ' . $this->logFile;
            $this->updateStatus();
            $this->fail(new \RuntimeException('Restore fallido. Ver ' . $this->logFile));
        }
    }

    private function findSqlFile(string $dir): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'sql') {
                return $file->getPathname();
            }
        }

        return null;
    }

    private function restoreMysql(string $sqlFile): bool
    {
        $config = config('database.connections.mysql');
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = sprintf(
            'mysql --host=%s --port=%d --user=%s --password=%s %s < "%s"',
            $host,
            (int) $port,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            $sqlFile
        );

        $this->log('Ejecutando: mysql import (password omitido en logs)');

        $process = Process::fromShellCommandline($command, null, null, null, 0);
        $output = '';
        $exitCode = 1;

        try {
            $process->run(function ($type, $buffer) use (&$output) {
                $output .= $buffer;
            });
            $exitCode = $process->getExitCode();
        } catch (\Exception $e) {
            $this->log('ERROR mysql: ' . $e->getMessage());
            return false;
        }

        if ($exitCode !== 0) {
            $this->log('ERROR mysql exit code: ' . $exitCode);
            $this->log('mysql output: ' . substr(trim($output), 0, 1000));
            return false;
        }

        $this->log('MySQL import exit code: 0 (éxito)');
        return true;
    }

    private function restorePgsql(string $sqlFile): bool
    {
        $config = config('database.connections.pgsql');
        $host = $config['host'] ?? '127.0.0.1';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $env = ['PGPASSWORD' => $password];

        $command = sprintf(
            'psql -U %s -h %s -d %s -f "%s"',
            escapeshellarg($username),
            escapeshellarg($host),
            escapeshellarg($database),
            $sqlFile
        );

        $this->log('Ejecutando: psql import (PGPASSWORD via env, no en logs)');

        $process = new Process(explode(' ', $command), null, $env, null, 0);
        $output = '';
        $exitCode = 1;

        try {
            $process->run(function ($type, $buffer) use (&$output) {
                $output .= $buffer;
            });
            $exitCode = $process->getExitCode();
        } catch (\Exception $e) {
            $this->log('ERROR psql: ' . $e->getMessage());
            return false;
        }

        if ($exitCode !== 0) {
            $this->log('ERROR psql exit code: ' . $exitCode);
            $this->log('psql output: ' . substr(trim($output), 0, 1000));
            return false;
        }

        $this->log('PostgreSQL import exit code: 0 (éxito)');
        return true;
    }

    private function cleanupTemp(string $tempDir): void
    {
        if (is_dir($tempDir)) {
            $this->deleteDirectory($tempDir);
            $this->log('Carpeta temporal eliminada: ' . $tempDir);
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    private function log(string $message): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $logLine = "[{$timestamp}] {$message}" . PHP_EOL;

        // Escribir al log estándar
        Log::channel('single')->info($message, ['job' => 'RestoreDatabaseJob', 'operation' => $this->operationId]);

        // Escribir a archivo dedicado
        $logPath = storage_path('logs/' . $this->logFile);
        file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
    }

    private function updateStatus(): void
    {
        Cache::put(
            'restore-status-' . $this->operationId,
            [
                'status' => $this->status,
                'safety_backup' => $this->safetyBackupPath,
                'error' => $this->errorDetails,
                'log_file' => $this->logFile,
                'updated_at' => now()->toIso8601String(),
            ],
            3600
        );
    }

    public function failed(?Throwable $exception = null): void
    {
        $this->status = 'failed';
        $this->errorDetails = $exception ? $exception->getMessage() : 'Job falló';
        $this->updateStatus();
        $this->log('Job falló: ' . $this->errorDetails);
    }
}
