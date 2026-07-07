<?php

use App\Http\Controllers\AdjuntoController;
use App\Http\Controllers\Admin\EstadoPalomaController;
use App\Http\Controllers\NovedadesController;
use App\Http\Controllers\GuardiaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoriaDocumentoController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MantenimientoVehiculoController;
use App\Http\Controllers\OrganismoController;
use App\Http\Controllers\PalomaController;
use App\Http\Controllers\PalomarController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SalidaVehiculoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VisitanteController;
use App\Http\Controllers\VueloController;
use Illuminate\Support\Facades\Route;

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


require __DIR__ . '/settings.php';
//Auth::routes();

// Rutas protegidas
Route::middleware('auth')->group(function () {

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/', [AdminController::class, 'index'])->name('index');

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
            Route::post('/',              [AdjuntoController::class, 'store'])->name('store');
            Route::delete('/{adjunto}',   [AdjuntoController::class, 'destroy'])->name('destroy');
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
            Route::get('/create',           [NovedadesController::class, 'create'])->name('create');
            Route::post('/',                [NovedadesController::class, 'store'])->name('store');
            Route::get('/{novedad}',        [NovedadesController::class, 'show'])->name('show');
            Route::get('/{novedad}/edit',   [NovedadesController::class, 'edit'])->name('edit');
            Route::put('/{novedad}',        [NovedadesController::class, 'update'])->name('update');
            Route::delete('/{novedad}',     [NovedadesController::class, 'destroy'])->name('destroy');
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
        // Vehículos - CRUD completo
        Route::prefix('vehiculos')->name('vehiculos.')->group(function () {
            Route::get('/', [VehiculoController::class, 'index'])->name('index');
            Route::get('/create', [VehiculoController::class, 'create'])->name('create');
            Route::post('/', [VehiculoController::class, 'store'])->name('store');
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

        // Rutas para administra los documentos
        Route::prefix('documentos')->name('documentos.')->group(function () {
            Route::get('/', [DocumentoController::class, 'index'])->name('index');
            Route::get('/create', [DocumentoController::class, 'create'])->name('create');
            Route::post('/', [DocumentoController::class, 'store'])->name('store');
            Route::get('/trashed', [DocumentoController::class, 'trashed'])->name('trashed');
            Route::get('/{documento}/preview', [DocumentoController::class, 'preview'])->name('preview'); // ← esta faltaba
            Route::get('/{documento}/download', [DocumentoController::class, 'download'])->name('download');
            Route::delete('/{documento}', [DocumentoController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [DocumentoController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [DocumentoController::class, 'forceDelete'])->name('force-delete');
        });
        Route::prefix('documentos/categorias')->name('documentos.categorias.')->group(function () {
            Route::get('/', [CategoriaDocumentoController::class, 'index'])->name('index');
            Route::get('/create', [CategoriaDocumentoController::class, 'create'])->name('create');
            Route::post('/', [CategoriaDocumentoController::class, 'store'])->name('store');
            Route::get('/{categoria}/edit', [CategoriaDocumentoController::class, 'edit'])->name('edit');
            Route::put('/{categoria}', [CategoriaDocumentoController::class, 'update'])->name('update');
            Route::delete('/{categoria}', [CategoriaDocumentoController::class, 'destroy'])->name('destroy');
        });
    });
});