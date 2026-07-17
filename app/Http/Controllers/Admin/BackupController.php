<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct()
    {
        // Solo admins pueden acceder a backups
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->isAdmin()) {
                abort(403, 'Solo administradores pueden gestionar backups.');
            }
            return $next($request);
        });
    }

    /**
     * Mostrar el panel de gestión de backups.
     */
    public function index()
    {
        $backups = $this->getBackups();

        return view('admin.backup', compact('backups'));
    }

    /**
     * Crear un backup manualmente.
     */
    public function create(Request $request)
    {
        $result = Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        if ($result === 0) {
            return redirect()->route('admin.backup.index')
                ->with('success', 'Backup iniciado. Se completará en segundo plano.');
        }

        return redirect()->route('admin.backup.index')
            ->with('error', 'Error al iniciar el backup. Revisá los logs.');
    }

    /**
     * Eliminar un backup.
     */
    public function delete($filename)
    {
        $disk = Storage::disk('backup');

        if ($disk->exists($filename)) {
            $disk->delete($filename);
        }

        return redirect()->route('admin.backup.index')
            ->with('success', 'Backup eliminado correctamente.');
    }

    /**
     * Ejecutar la limpieza automática de backups viejos.
     */
    public function cleanup()
    {
        Artisan::call('backup:clean');

        return redirect()->route('admin.backup.index')
            ->with('success', 'Limpieza de backups viejos completada.');
    }

    /**
     * Obtener la lista de backups desde el disk.
     */
    private function getBackups()
    {
        $disk = Storage::disk('backup');
        $files = $disk->allFiles();

        $backups = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'filename' => $file,
                    'name'     => pathinfo($file, PATHINFO_FILENAME),
                    'size'     => $disk->size($file),
                    'modified' => $disk->lastModified($file),
                ];
            }
        }

        usort($backups, function ($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });

        return $backups;
    }
}
