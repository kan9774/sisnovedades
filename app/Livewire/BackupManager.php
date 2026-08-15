<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class BackupManager extends Component
{
    public $backups = [];
    public $isRunning = false;
    public $message = '';
    public $messageType = '';

    public $showRestoreModal = false;
    public $selectedBackup = null;
    public $restoreStatus = 'idle';
    public $restoreSafetyPath = null;
    public $restoreError = null;
    public $restoreLogPath = null;
    public $operationId = '';
    public $restoreConfirmationText = '';
    public $currentDriver = '';
    public $detectedBackupDriver = '';

    public function mount()
    {
        $this->loadBackups();
        $this->currentDriver = config('database.default', 'mysql');
    }

    public function loadBackups()
    {
        $disk = Storage::disk('backup');
        $files = $disk->allFiles();

        $this->backups = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $this->backups[] = [
                    'filename' => $file,
                    'name'     => pathinfo($file, PATHINFO_FILENAME),
                    'size'     => $this->formatSize($disk->size($file)),
                    'size_bytes' => $disk->size($file),
                    'modified' => $disk->lastModified($file),
                    'date'     => date('d/m/Y H:i', $disk->lastModified($file)),
                ];
            }
        }

        usort($this->backups, function ($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });
    }

    public function createBackup()
    {
        $this->isRunning = true;
        $this->message = '';
        $this->messageType = '';

        $process = proc_open(
            'php artisan backup:run --only-db --no-interaction 2>&1',
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (is_resource($process)) {
            while (!feof($pipes[1])) {
                $output = fgets($pipes[1]);
                if (strpos($output, 'Finished') !== false || strpos($output, 'error') !== false) {
                    $this->message = trim($output);
                    $this->messageType = (strpos($output, 'error') !== false) ? 'danger' : 'success';
                }
            }
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }

        $this->isRunning = false;
        $this->loadBackups();
        $this->dispatch('refresh-backups');
    }

    public function quickCreate()
    {
        Artisan::call('backup:run', ['--only-db' => true]);
        $this->message = '⚡ Backup completado correctamente.';
         $this->messageType = 'success';
        $this->dispatch('refresh-backups');
         $this->loadBackups();
    }

    public function deleteBackup($filename)
    {
        $disk = Storage::disk('backup');
        if ($disk->exists($filename)) {
            $disk->delete($filename);
        }
        $this->message = '✅ Backup eliminado correctamente.';
        $this->messageType = 'success';
        $this->loadBackups();
        $this->dispatch('refresh-backups');
    }

    public function runCleanup()
    {
        Artisan::call('backup:clean');
        $this->message = '🧹 Limpieza de backups viejos completada.';
        $this->messageType = 'success';
        $this->dispatch('refresh-backups');
    }

    public function openRestore($filename)
    {
        if (!Auth::user()->isAdmin()) {
            $this->message = 'No tenés permisos para restaurar backups.';
            $this->messageType = 'danger';
            return;
        }

        $this->selectedBackup = $filename;
        $this->restoreConfirmationText = '';
        $this->restoreStatus = 'idle';
        $this->restoreSafetyPath = null;
        $this->restoreError = null;
        $this->restoreLogPath = null;

        $backupName = pathinfo($filename, PATHINFO_FILENAME);
        $this->detectedBackupDriver = $this->detectDriverFromFilename($filename);
        $this->currentDriver = config('database.default', 'mysql');

        $this->showRestoreModal = true;
    }

    public function closeRestoreModal()
    {
        $this->showRestoreModal = false;
        $this->selectedBackup = null;
        $this->restoreStatus = 'idle';
        $this->restoreConfirmationText = '';
    }

    public function startRestore()
    {
        if (!Auth::user()->isAdmin()) {
            $this->message = 'No tenés permisos para restaurar backups.';
            $this->messageType = 'danger';
            return;
        }

        $this->operationId = uniqid('restore_');
        $this->restoreStatus = 'starting';

        \App\Jobs\RestoreDatabaseJob::dispatch($this->selectedBackup, $this->operationId)->onQueue('default');

        $this->showRestoreModal = false;
        $this->message = 'Restauración iniciada. No cierres esta página.';
        $this->messageType = 'info';
    }

    public function getRestoreStatus()
    {
        $status = Cache::get('restore-status-' . $this->operationId);
        if ($status) {
            $this->restoreStatus = $status['status'] ?? 'idle';
            $this->restoreSafetyPath = $status['safety_backup'] ?? null;
            $this->restoreError = $status['error'] ?? null;
            $this->restoreLogPath = $status['log_file'] ?? null;
        }
        return $this->restoreStatus;
    }

    public function detectDriverFromFilename(string $filename): string
    {
        if (stripos($filename, 'mysql') !== false) {
            return 'mysql';
        }
        if (stripos($filename, 'pgsql') !== false || stripos($filename, 'postgres') !== false) {
            return 'pgsql';
        }
        return 'mysql';
    }

    public function render()
    {
        return view('livewire.backup-manager');
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
