<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupManager extends Component
{
    public $backups = [];
    public $isRunning = false;
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        $this->loadBackups();
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
        $this->message = '⚡ Backup iniciado en segundo plano.';
        $this->messageType = 'info';
        $this->dispatch('refresh-backups');
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

    public function render()
    {
        return view('livewire.backup-manager');
    }
}
