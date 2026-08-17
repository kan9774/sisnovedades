<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\AdjuntoController;
use App\Http\Controllers\ForzarCambioPasswordController;
use App\Http\Controllers\NovedadesController;
use App\Http\Controllers\GuardiaController;
use App\Http\Controllers\UnidadController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MantenimientoVehiculoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NovedadPersonalController;
use App\Http\Controllers\NovedadRanchoController;
use App\Livewire\Palomas\PalomaShow;
use App\Models\Palomar;
use App\Support\PalomarPdfGenerator;
use App\Http\Controllers\VehiculoController;

use App\Livewire\Guardias;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Inventario\ItemsCatalogo;
use App\Livewire\Inventario\MovimientosInventario;
use App\Livewire\Inventario\UnidadesIndividuales;
use App\Livewire\Inventario\CategoriasCatalogo;
use App\Livewire\Inventario\ListadoDepositoGeneral;
use App\Livewire\Inventario\UbicacionesCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Pública
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
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
Route::middleware(['auth'])->group(function () {
    Route::get('/password/forzar-cambio', [ForzarCambioPasswordController::class, 'edit'])
        ->name('password.forzar-cambio');
    Route::put('/password/forzar-cambio', [ForzarCambioPasswordController::class, 'update'])
        ->name('password.forzar-cambio.update');
});
Route::middleware(['auth', 'verified.if-enabled', 'require.password-change'])->group(function () {
    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('inventario')->name('inventario.')->group(function () {
            Route::get('/items', function () {
                return view('livewire.inventario.items-layout');
            })->name('items');

            Route::get('/movimientos', function () {
                return view('livewire.inventario.movimientos-layout');
            })->name('movimientos');

            Route::get('/unidades-individuales', function () {
                return view('livewire.inventario.unidades-layout');
            })->name('unidades-individuales');

            Route::get('/ubicaciones', function () {
                return view('livewire.inventario.ubicaciones-layout');
            })->name('ubicaciones');

            Route::get('/categorias', function () {
                return view('livewire.inventario.categorias-layout');
            })->name('categorias');
            Route::get('/tallas', function () {
                return view('livewire.inventario.tallas-layout');
            })->name('tallas');
            Route::get('/proveedores', function () {
                return view('livewire.inventario.proveedores-layout');
            })->name('proveedores');

            // Entregas y devoluciones (carrito multi-ítem)
            Route::get('/entregas', function () {
                return view('livewire.inventario.entregas-layout');
            })->name('entregas');
            Route::get('/entregas/{entrega}/comprobante', [EntregaController::class, 'comprobante'])
                ->name('entregas.comprobante');
            Route::get('/lotes', function () {
                return view('livewire.inventario.lotes-layout');
            })->name('lotes');
            Route::get('/vencidos-terceros', function () {
                return view('livewire.inventario.vencidos-terceros-layout');
            })->name('vencidos-terceros');
            Route::get('/entregas/historial', function () {
                return view('livewire.inventario.entregas-historial-layout');
            })->name('entregas.historial');
            Route::get('/items/plantilla', function () {
                $ruta = (new \App\Exports\ItemsPlantillaExport())->generar();
                return response()->download($ruta, 'plantilla_items.xlsx')->deleteFileAfterSend(true);
            })->name('items.plantilla');

            Route::get('/unidades-individuales/plantilla', function () {
                $ruta = (new \App\Exports\ItemUnidadesPlantillaExport())->generar();
                return response()->download($ruta, 'plantilla_item_unidades.xlsx')->deleteFileAfterSend(true);
            })->name('unidades-individuales.plantilla');
        });
        Route::prefix('grados')->name('grados.')->group(function () {
            Route::get('/', function () {
                return view('livewire.grados.grados-layout');
            })->name('index');
        });
        Route::prefix('jefes-unidad')->name('jefes-unidad.')->group(function () {
            Route::get('/', function () {
                return view('livewire.admin.jefes-unidad.jefes-unidad-layout');
            })->name('index');
        });
        Route::get('/', function () {
            $dashboard = new App\Livewire\AdminDashboard();
            $dashboard->mount();

            return view('admin.index', [
                'guardiaHoy' => $dashboard->guardiaHoy,
                'vehiculosEnRuta' => $dashboard->vehiculosEnRuta,
                'totalConductores' => $dashboard->totalConductores,
                'vuelosActivos' => $dashboard->vuelosActivos,
                'conductoresAlertas' => $dashboard->conductoresAlertas,
                'ultimosVuelos' => $dashboard->ultimosVuelos,
                'ultimasNovedades' => [],
            ]);
        })->name('index');

        // Auditoría de acciones del sistema
        Route::get('/logs', function () {
            return view('livewire.logs.layout');
        })->name('logs.index');

        // Gestión de backups
        Route::get('/backup',      [BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup',      [BackupController::class, 'create'])->name('backup.create');
        Route::post('/backup/clean', [BackupController::class, 'cleanup'])->name('backup.cleanup');
        Route::post('/backup/{filename}/delete', [BackupController::class, 'delete'])->name('backup.delete');

        // Notificaciones
        Route::get('/notificaciones', function () {
            return view('livewire.notificaciones.layout');
        })->name('notificaciones.index');

        Route::post('/notificaciones/{id}/leer', function (string $id) {
            $notificacion = auth()->user()->notifications()->findOrFail($id);
            [$novedadId, $guardiaId] = \App\Services\NovedadService::marcarLeida($notificacion);

            if ($novedadId && $guardiaId) {
                return redirect()->route('admin.guardias.novedades.show', [$guardiaId, $novedadId]);
            }

            return back();
        })->name('notificaciones.leer');

        Route::post('/notificaciones/marcar-todas', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
        })->name('notificaciones.marcar-todas');

        // Novedades (vista general)
        Route::get('/novedades', [NovedadesController::class, 'index'])->name('novedades.index');
        // Usuarios
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/create/{user}/resume', function (?User $user = null) {
                return view('livewire.admin.users.create', ['user' => $user]);
            })->name('create.resume');

            Route::delete('/{id}/incompleto', [\App\Livewire\Admin\Users::class, 'destroyIncompleto'])
                ->name('destroy-incompleto');
            Route::get('/', function () {
                return view('livewire.admin.users.layout');
            })->name('index');
            Route::get('/create', function () {
                \Illuminate\Support\Facades\Gate::authorize('create', \App\Models\User::class);
                return view('livewire.admin.users.create');
            })->name('create');
            Route::get('/{id}/edit', function ($id) {
                return view('livewire.admin.users.edit', ['user' => User::findOrFail($id)]);
            })->name('edit');
            Route::post('/{id}/restore',        [\App\Livewire\Admin\Users::class, 'restaurar'])->name('restore');
            Route::delete('/{id}/force-delete', [\App\Livewire\Admin\Users::class, 'ejecutarEliminacionPermanente'])->name('force-delete');
            Route::delete('/{id}',              [\App\Livewire\Admin\Users::class, 'confirmarEliminacion'])->name('destroy');
        });
        // Roles — solo admin
        Route::get('/roles', function () {
            return view('livewire.roles.layout');
        })->name('roles.index');
        // Permisos — solo admin
        Route::get('/permisos', function () {
            return view('livewire.permisos.layout');
        })->name('permisos.index');

        // Destinatarios de PDF
        Route::get('/pdf-destinatarios', function () {
            return view('livewire.pdf-destinatarios.layout');
        })->name('pdf-destinatarios');

        // Adjuntos
        Route::prefix('guardias/{guardia}/novedades/{novedad}/adjuntos')->name('adjuntos.')->group(function () {
            Route::get('/{adjunto}/download', [AdjuntoController::class, 'download'])->name('download');
        });

        // Guardias — Livewire (CRUD + papelera + cerrar/reactivar/pdf) + Controller (show/destroy/hoy/pdf)
        Route::get('/guardias', function () {
            return view('livewire.guardias.layout');
        })->name('guardias.index');

        Route::get('/guardias/hoy', [GuardiaController::class, 'hoy'])->name('guardias.hoy');

        Route::get('guardias/{guardia}', [GuardiaController::class, 'show'])->name('guardias.show');
        Route::delete('guardias/{guardia}', [GuardiaController::class, 'destroy'])->name('guardias.destroy');

        Route::get('/guardias/{guardia}/pdf', [GuardiaController::class, 'pdf'])->name('guardias.pdf');


        // Novedades anidadas bajo guardia
        Route::prefix('guardias/{guardia}/novedades')->name('guardias.novedades.')->group(function () {
            Route::get('/{novedad}',        [NovedadesController::class, 'show'])->name('show');
            Route::delete('/{novedad}',     [NovedadesController::class, 'destroy'])->name('destroy');
            Route::post('/{novedad}/tomar', [NotificationController::class, 'tomar'])->name('tomar');
        });

        // Organismos (Livewire, formulario inline sin modales)
        Route::get('/organismos', function () {
            return view('livewire.organismos.layout');
        })->name('organismos.index');

        // Oficinas (catálogo, para notificaciones de novedades)
        Route::get('/oficinas', function () {
            return view('livewire.oficinas.layout');
        })->name('oficinas.index');
        // Tipos de vehículo (catálogo) - debe ir ANTES del grupo vehiculos/{vehiculo}
        Route::get('/vehiculos/tipos', function () {
            return view('livewire.vehiculos.tipos.layout');
        })->name('vehiculos.tipos.index');

        // Unidades - listado/alta/edición/borrado en Livewire (formulario inline, sin modales)
        Route::get('/unidades', function () {
            return view('livewire.unidades.layout');
        })->name('unidades.index');

        // Detalle de unidad (sin migrar, sigue usando UnidadController@show)
        Route::resource('unidades', UnidadController::class)
            ->only(['show'])
            ->parameters(['unidades' => 'unidad']);

        // Vehículos - Livewire
        Route::prefix('vehiculos')->name('vehiculos.')->group(function () {
            Route::get('/', function () {
                return view('livewire.vehiculos.layout');
            })->name('index');

            // Mantenimientos (anidados bajo vehiculo)
            Route::prefix('{vehiculo}/mantenimientos')->name('mantenimientos.')->group(function () {
                Route::get('/', [MantenimientoVehiculoController::class, 'index'])->name('index');
                Route::delete('/{mantenimiento}', [MantenimientoVehiculoController::class, 'destroy'])->name('destroy');
            });
        });

        // Conductores - Livewire
        Route::get('/conductores', function () {
            return view('livewire.conductores.layout');
        })->name('conductores.index');

        // Novedades de personal y rancho (anidadas a guardia)
        Route::prefix('guardias/{guardia}')->name('guardias.')->group(function () {
            Route::post('personal', [NovedadPersonalController::class, 'store'])->name('personal.store');
            Route::delete('personal/{novedadPersonal}', [NovedadPersonalController::class, 'destroy'])->name('personal.destroy');
            Route::put('rancho', [NovedadRanchoController::class, 'update'])->name('rancho.update');
        });
        // Palomar
        Route::prefix('palomar')->group(function () {
            // Listado Livewire
            Route::get('palomares', function () {
                return view('livewire.palomares-layout');
            })->name('palomares.index');

            // Reporte PDF (fuera de Livewire)
            Route::get('palomares/{palomar}/reporte', function (Palomar $palomar) {
                return PalomarPdfGenerator::generar($palomar)
                    ->stream(PalomarPdfGenerator::nombreArchivo($palomar));
            })->name('palomares.reporte');

            // Livewire: listado + CRUD inline
            Route::get('palomas', function () {
                return view('livewire.palomas-layout');
            })->name('palomas.index');
            // Livewire: detalle de paloma (datos + historial + vuelos)
            Route::get('palomas/{paloma}', PalomaShow::class)->name('palomas.show');

            // Livewire: listado + CRUD inline
            Route::get('vuelos', function () {
                return view('livewire.vuelos.layout');
            })->name('vuelos.index');

            // Rutas personalizadas de vuelos (ANTES del resource, mismo criterio que palomares.reporte)
            Route::get('vuelos/{vuelo}/resultados', function (\App\Models\Vuelo $vuelo) {
                return view('livewire.vuelos.resultados', ['vueloId' => $vuelo->id]);
            })->name('vuelos.resultados');

            // VueloController desactivado — rutas reemplazadas por Livewire\Vuelos

            // Estados de paloma (Livewire, formulario inline sin modales)
            Route::get('estados-paloma', function () {
                return view('livewire.palomar.estados.layout');
            })->name('palomar.estados-paloma.index');
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
