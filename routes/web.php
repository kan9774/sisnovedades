<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdjuntoController;
use App\Http\Controllers\Admin\EstadoPalomaController;
use App\Http\Controllers\NovedadesController;
use App\Http\Controllers\GuardiaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MantenimientoVehiculoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NovedadPersonalController;
use App\Http\Controllers\NovedadRanchoController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\OrganismoController;
use App\Http\Controllers\PalomaController;
use App\Http\Controllers\PalomarController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SalidaVehiculoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VisitanteController;
use App\Http\Controllers\VueloController;
use App\Models\Documento;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Pública
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    // Visitante
    Route::get('/novedades-publicas', [VisitanteController::class, 'index'])->name('novedades-publicas');
    Route::get('/guardias-publicas/{guardia}', [VisitanteController::class, 'show'])
        ->name('guardias-publicas.show');
    Route::get('/guardias-publicas/{guardia}/novedades/{novedad}', [VisitanteController::class, 'showNovedad'])
        ->name('guardias-publicas.novedades.show');
    Route::get('/guardias-publicas/{guardia}/novedades/{novedad}/adjuntos/{adjunto}/download', [VisitanteController::class, 'downloadAdjunto'])
        ->name('guardias-publicas.adjuntos.download');
    Route::get('/guardias/{guardia}/novedades/{novedad}/adjuntos/{adjunto}/view', [AdjuntoController::class, 'view'])
        ->name('guardias-publicas.adjuntos.view');
});


// PDF preview de guardia cerrada (público)
Route::get('/guardias-publicas/{guardia}/pdf-preview', function (\App\Models\Guard $guardia) {
    abort_if($guardia->status !== 'closed', 403);

    $guardia->load([
        'capitan',
        'oficial',
        'escribiente',
        'novedades.organismo',
        'novedadesPersonal',
        'novedadesRancho.unidad',
        'ranchoMenu',
        'salidasVehiculos.vehiculo',
        'salidasVehiculos.conductor',
    ]);

    return response()->view('admin.guardias.pdf.novedades', ['guardia' => $guardia])
        ->header('Content-Type', 'text/html')
        ->header('X-Frame-Options', 'SAMEORIGIN');
})->name('guardias-publicas.pdf-preview');

require __DIR__ . '/settings.php';
//Auth::routes();

// Rutas protegidas
Route::middleware(['auth', 'verified.if-enabled'])->group(function () {

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/', [AdminController::class, 'index'])->name('index');

        // Auditoría de acciones del sistema
        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');

        // Notificaciones
        Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
            Route::get('/',                [NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/leer',      [NotificationController::class, 'markAsRead'])->name('leer');
            Route::post('/marcar-todas',   [NotificationController::class, 'markAllAsRead'])->name('marcar-todas');
        });

        // Novedades (vista general)
        Route::get('/novedades', [NovedadesController::class, 'index'])->name('novedades.index');



        // Usuarios
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',                     [UserController::class, 'index'])->name('index');
            Route::get('/create',               [UserController::class, 'create'])->name('create');
            Route::post('/',                    [UserController::class, 'store'])->name('store'); // ← falta esta
            Route::get('/{id}/edit',            [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}',                 [UserController::class, 'update'])->name('update'); // ← y esta
            Route::post('/{id}/restore',        [UserController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])->name('force-delete');
            Route::delete('/{id}',              [UserController::class, 'destroy'])->name('destroy');
            Route::get('/userdelete',           [UserController::class, 'UserDelete'])->name('userdelete');
        });
        // Roles — solo admin
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',              [RolController::class, 'index'])->name('index');
            Route::get('/create',        [RolController::class, 'create'])->name('create');
            Route::post('/',             [RolController::class, 'store'])->name('store');
            Route::get('/{rol}/edit',    [RolController::class, 'edit'])->name('edit');
            Route::put('/{rol}',         [RolController::class, 'update'])->name('update');
            Route::delete('/{rol}',      [RolController::class, 'destroy'])->name('destroy');
        });
        // Permisos — solo admin
        Route::prefix('permisos')->name('permisos.')->group(function () {
            Route::get('/',                  [PermisoController::class, 'index'])->name('index');
            Route::get('/create',            [PermisoController::class, 'create'])->name('create');
            Route::post('/',                 [PermisoController::class, 'store'])->name('store');
            Route::get('/{permiso}/edit',    [PermisoController::class, 'edit'])->name('edit');
            Route::put('/{permiso}',         [PermisoController::class, 'update'])->name('update');
            Route::delete('/{permiso}',      [PermisoController::class, 'destroy'])->name('destroy');
        });

        // Adjuntos
        Route::prefix('guardias/{guardia}/novedades/{novedad}/adjuntos')->name('adjuntos.')->group(function () {
            Route::get('/{adjunto}/download', [AdjuntoController::class, 'download'])->name('download');
        });

        // Verificar que todas usen GuardiaController (con doble 'l')
        Route::get('/guardias/hoy', [GuardiaController::class, 'hoy'])->name('guardias.hoy');
        Route::get('/guardias/trashed', [GuardiaController::class, 'trashed'])->name('guardias.trashed');
        Route::resource('guardias', GuardiaController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/guardias/{guardia}/cerrar', [GuardiaController::class, 'cerrar'])->name('guardias.cerrar');
        Route::post('/guardias/{guardia}/reactivar', [GuardiaController::class, 'reactivar'])->name('guardias.reactivar');
        Route::get('/guardias/{guardia}/pdf', [GuardiaController::class, 'pdf'])->name('guardias.pdf');

        Route::post('/guardias/{id}/restore', [GuardiaController::class, 'restore'])->name('guardias.restore');
        Route::delete('/guardias/{id}/force-delete', [GuardiaController::class, 'forceDelete'])->name('guardias.force-delete');
        Route::delete('/guardias/{guardia}', [GuardiaController::class, 'destroy'])->name('guardias.destroy');


        // Novedades anidadas bajo guardia
        Route::prefix('guardias/{guardia}/novedades')->name('guardias.novedades.')->group(function () {
            Route::get('/{novedad}',        [NovedadesController::class, 'show'])->name('show');
            Route::delete('/{novedad}',     [NovedadesController::class, 'destroy'])->name('destroy');
            Route::post('/{novedad}/tomar', [NotificationController::class, 'tomar'])->name('tomar');
        });

        // Organismos
        Route::prefix('organismos')->name('organismos.')->group(function () {
            Route::get('/',              [OrganismoController::class, 'index'])->name('index');
            Route::get('/create',        [OrganismoController::class, 'create'])->name('create');
            Route::post('/',             [OrganismoController::class, 'store'])->name('store');
            Route::get('/{organismo}/edit',  [OrganismoController::class, 'edit'])->name('edit');
            Route::put('/{organismo}',       [OrganismoController::class, 'update'])->name('update');
            Route::delete('/{organismo}',    [OrganismoController::class, 'destroy'])->name('destroy');
        });

        // Oficinas (catálogo, para notificaciones de novedades)
        Route::prefix('oficinas')->name('oficinas.')->group(function () {
            Route::get('/',              [OficinaController::class, 'index'])->name('index');
            Route::get('/create',        [OficinaController::class, 'create'])->name('create');
            Route::post('/',             [OficinaController::class, 'store'])->name('store');
            Route::get('/{oficina}/edit', [OficinaController::class, 'edit'])->name('edit');
            Route::put('/{oficina}',     [OficinaController::class, 'update'])->name('update');
            Route::delete('/{oficina}',  [OficinaController::class, 'destroy'])->name('destroy');
        });
        // Tipos de vehículo (catálogo) - debe ir ANTES del grupo vehiculos/{vehiculo}
        Route::prefix('vehiculos/tipos')->name('vehiculos.tipos.')->group(function () {
            Route::get('/', [TipoVehiculoController::class, 'index'])->name('index');
            Route::get('/create', [TipoVehiculoController::class, 'create'])->name('create');
            Route::post('/', [TipoVehiculoController::class, 'store'])->name('store');
            Route::get('/{tipo}/edit', [TipoVehiculoController::class, 'edit'])->name('edit');
            Route::put('/{tipo}', [TipoVehiculoController::class, 'update'])->name('update');
            Route::delete('/{tipo}', [TipoVehiculoController::class, 'destroy'])->name('destroy');
        });

        // Vehículos - CRUD completo
        Route::prefix('vehiculos')->name('vehiculos.')->group(function () {
            Route::get('/', [VehiculoController::class, 'index'])->name('index');
            Route::get('/create', [VehiculoController::class, 'create'])->name('create');
            Route::post('/', [VehiculoController::class, 'store'])->name('store');
            Route::get('/export', [VehiculoController::class, 'export'])->name('export');
            Route::get('/{vehiculo}', [VehiculoController::class, 'show'])->name('show');
            Route::get('/{vehiculo}/edit', [VehiculoController::class, 'edit'])->name('edit');
            Route::put('/{vehiculo}', [VehiculoController::class, 'update'])->name('update');
            Route::delete('/{vehiculo}', [VehiculoController::class, 'destroy'])->name('destroy');

            // Mantenimientos (anidados bajo vehiculo)
            Route::prefix('{vehiculo}/mantenimientos')->name('mantenimientos.')->group(function () {
                Route::get('/', [MantenimientoVehiculoController::class, 'index'])->name('index');
                Route::get('/create', [MantenimientoVehiculoController::class, 'create'])->name('create');
                Route::post('/', [MantenimientoVehiculoController::class, 'store'])->name('store');
                Route::get('/{mantenimiento}/edit', [MantenimientoVehiculoController::class, 'edit'])->name('edit');
                Route::put('/{mantenimiento}', [MantenimientoVehiculoController::class, 'update'])->name('update');
                Route::delete('/{mantenimiento}', [MantenimientoVehiculoController::class, 'destroy'])->name('destroy');
            });
        });

        // Conductores - CRUD completo
        Route::resource('conductores', ConductorController::class)
            ->except(['show'])
            ->parameters(['conductores' => 'conductor']); // Opcional: si no necesitas vista show

        // Salidas de vehículos (anidadas a guardia)
        Route::prefix('guardias/{guardia}/salidas')->name('guardias.salidas.')->group(function () {
            Route::get('/create', [SalidaVehiculoController::class, 'create'])->name('create');
            Route::post('/', [SalidaVehiculoController::class, 'store'])->name('store');
            Route::get('/{salida}/edit', [SalidaVehiculoController::class, 'edit'])->name('edit');
            Route::put('/{salida}', [SalidaVehiculoController::class, 'update'])->name('update');
            Route::delete('/{salida}', [SalidaVehiculoController::class, 'destroy'])->name('destroy');
        });

        // Novedades de personal y rancho (anidadas a guardia)
        Route::prefix('guardias/{guardia}')->name('guardias.')->group(function () {
            Route::post('personal', [NovedadPersonalController::class, 'store'])->name('personal.store');
            Route::delete('personal/{novedadPersonal}', [NovedadPersonalController::class, 'destroy'])->name('personal.destroy');
            Route::put('rancho', [NovedadRanchoController::class, 'update'])->name('rancho.update');
        });
        // Palomar
        Route::prefix('palomar')->group(function () {
            // Primero la ruta personalizada del reporte (debe ir ANTES que el resource)
            Route::get('palomares/{palomar}/reporte', [PalomarController::class, 'reporte'])
                ->name('palomares.reporte');

            Route::resource('palomares', PalomarController::class)
                ->parameters(['palomares' => 'palomar']);

            Route::resource('palomas', PalomaController::class)
                ->parameters(['palomas' => 'paloma']);

            // Rutas personalizadas de vuelos (ANTES del resource, mismo criterio que palomares.reporte)
            Route::get('vuelos/{vuelo}/resultados', [VueloController::class, 'resultados'])
                ->name('vuelos.resultados');
            Route::post('vuelos/{vuelo}/resultados', [VueloController::class, 'guardarResultados'])
                ->name('vuelos.guardar-resultados');

            Route::resource('vuelos', VueloController::class)
                ->parameters(['vuelos' => 'vuelo']);

            Route::resource('estados-paloma', EstadoPalomaController::class)
                ->parameters([
                    'estados-paloma' => 'estado'
                ]);
        });

        // Rutas para administrar los documentos (Livewire)
        Route::prefix('documentos')->name('documentos.')->group(function () {
            Route::get('/', function () {
                return view('livewire.documentos.layout');
            })->name('index');
            Route::get('/{documento}/download', function (Documento $documento) {
                return Storage::disk('public')->download(
                    $documento->archivo_path,
                    $documento->nombre_original
                );
            })->name('download');
        });
        Route::get('/documentos/categorias', function () {
            return view('livewire.categorias-documentos.layout');
        })->name('documentos.categorias.index');
    });
});
