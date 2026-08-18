<?php

use App\Models\Conductor;
use App\Models\Guard;
use App\Models\Item;
use App\Models\Paloma;
use App\Models\Palomar;
use App\Models\EstadoPaloma;
use App\Models\TipoCombustible;
use App\Models\TipoLubricante;
use App\Models\TipoRodado;
use App\Models\TipoVehiculo;
use App\Models\Unidad;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

test('permanent delete clears blocking fk tables', function () {
    // Enable foreign keys (SQLite disables them by default; MySQL enforces by default)
    if (DB::getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    $user = User::factory()->create([
        'name' => 'Test FK Delete',
        'email' => 'test-fk-delete-' . time() . '@test.com',
        'perfil_completo_at' => now(),
    ]);

    // Create dependent records first (to satisfy FK constraints)
    $palomarId = Palomar::create(['nombre' => 'Test Palomar'])->id;
    $estadoId = EstadoPaloma::firstOrCreate(['nombre' => 'Test Estado'], ['color' => 'black'])->id;
    $palomaId = Paloma::create([
        'palomar_id' => $palomarId,
        'anilla' => 'AN-' . time(),
        'estado_id' => $estadoId,
    ])->id;
    $ubicacionId = Ubicacion::create(['nombre' => 'Test Ubicacion', 'tipo' => 'deposito'])->id;
    $categoriaId = \App\Models\Categoria::firstOrCreate(['nombre' => 'Test Categoria'])->id;
    $itemId = Item::create(['codigo' => 'TEST-ITEM', 'nombre' => 'Test Item', 'categoria_id' => $categoriaId, 'tipo_seguimiento' => 'cantidad'])->id;
    $guardId = Guard::create(['date' => now()->format('Y-m-d'), 'captain_id' => $user->id, 'oficer_id' => $user->id, 'status' => 'open'])->id;

    // Insert data in the 4 blocking FK tables
    DB::table('historial_palomas')->insert([
        ['paloma_id' => $palomaId, 'user_id' => $user->id, 'evento' => 'cambio_estado', 'fecha_evento' => now(), 'observaciones' => 'test'],
    ]);
    DB::table('entregas')->insert([
        ['tipo' => 'entrega', 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('movimientos')->insert([
        ['item_id' => $itemId, 'tipo' => 'entrada', 'cantidad' => 1, 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('novedades_personal')->insert([
        ['guard_id' => $guardId, 'user_id' => $user->id, 'hora' => '10:00:00', 'tipo' => 'test', 'texto' => 'test'],
    ]);

    // Verify data exists
    expect(DB::table('historial_palomas')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('entregas')->where('usuario_id', $user->id)->count())->toBe(1);
    expect(DB::table('movimientos')->where('usuario_id', $user->id)->count())->toBe(1);
    expect(DB::table('novedades_personal')->where('user_id', $user->id)->count())->toBe(1);

    // Soft delete
    $user->delete();

    // Now permanent delete (simulating ejecutarEliminacionPermanente)
    DB::transaction(function () use ($user) {
        DB::table('historial_palomas')->where('user_id', $user->id)->delete();
        DB::table('entregas')->where('usuario_id', $user->id)->delete();
        DB::table('movimientos')->where('usuario_id', $user->id)->delete();
        DB::table('novedades_personal')->where('user_id', $user->id)->delete();
        $user->forceDelete();
    });

    // Verify everything was deleted
    expect(User::onlyTrashed()->where('id', $user->id)->count())->toBe(0);
    expect(User::withTrashed()->where('id', $user->id)->count())->toBe(0);
    expect(DB::table('historial_palomas')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('entregas')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('movimientos')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('novedades_personal')->where('user_id', $user->id)->count())->toBe(0);
});

test('permanent delete vehiculo clears salidas_vehiculos (FK RESTRICT)', function () {
    if (DB::getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    $tipoVehiculo = TipoVehiculo::firstOrCreate(['nombre' => 'Test Tipo Vehiculo']);
    $unidad = Unidad::firstOrCreate(['nombre' => 'Test Unidad']);
    $tipoCombustible = TipoCombustible::firstOrCreate(['nombre' => 'Test Combustible']);
    $tipoLubricante = TipoLubricante::firstOrCreate(['nombre' => 'Test Lubricante']);
    $tipoRodado = TipoRodado::firstOrCreate(['nombre' => 'Test Rodado']);
    $conductor = Conductor::create([
        'grado' => 'Suboficial',
        'primer_nombre' => 'Test',
        'primer_apellido' => 'Conductor',
        'documento' => '00000000',
        'nro_licencia' => '0000000',
        'categoria_licencia' => 'B',
        'fecha_vencimiento_licencia' => now()->addYear(),
        'activo' => true,
    ]);

    $guardia = Guard::create([
        'date' => now()->format('Y-m-d'),
        'captain_id' => 1,
        'oficer_id' => 1,
        'status' => 'open',
    ]);

    $vehiculo = Vehiculo::create([
        'matricula' => 'TEST-VH-' . time(),
        'marca' => 'Test Marca',
        'modelo' => 'Test Modelo',
        'tipo_vehiculo_id' => $tipoVehiculo->id,
        'unidad_id' => $unidad->id,
        'tipo_combustible_id' => $tipoCombustible->id,
        'tipo_lubricante_id' => $tipoLubricante->id,
        'tipo_rodado_id' => $tipoRodado->id,
        'estado' => 'verde',
        'activo' => true,
    ]);

    // Insert a salidas_vehiculo record (FK RESTRICT blocks forceDelete)
    DB::table('salidas_vehiculos')->insert([
        'guardia_id' => $guardia->id,
        'vehiculo_id' => $vehiculo->id,
        'conductor_id' => $conductor->id,
        'tipo_combustible' => 'gas_oil',
        'hora_sale' => '10:00:00',
        'comision' => 'Test comision',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Verify salidas_vehiculo exists
    expect(DB::table('salidas_vehiculos')->where('vehiculo_id', $vehiculo->id)->count())->toBe(1);

    // Soft delete
    $vehiculo->delete();

    // Permanent delete (simulating ejecutarEliminacionPermanente)
    DB::transaction(function () use ($vehiculo) {
        $vehiculo->salidas()->delete();
        $vehiculo->forceDelete();
    });

    // Verify everything was deleted
    expect(Vehiculo::onlyTrashed()->where('id', $vehiculo->id)->count())->toBe(0);
    expect(Vehiculo::withTrashed()->where('id', $vehiculo->id)->count())->toBe(0);
    expect(DB::table('salidas_vehiculos')->where('vehiculo_id', $vehiculo->id)->count())->toBe(0);
});

test('permanent delete user clears all blocking fk tables', function () {
    if (DB::getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    $user = User::factory()->create([
        'name' => 'Test User FK Delete',
        'email' => 'test-user-fk-delete-' . time() . '@test.com',
        'perfil_completo_at' => now(),
    ]);

    $guardId = Guard::create(['date' => now()->format('Y-m-d'), 'captain_id' => $user->id, 'oficer_id' => $user->id, 'status' => 'open'])->id;
    $unidadId = Unidad::firstOrCreate(['nombre' => 'Test Unidad 2'])->id;
    $palomarId = Palomar::create(['nombre' => 'Test Palomar'])->id;
    $estadoId = EstadoPaloma::firstOrCreate(['nombre' => 'Test Estado'], ['color' => 'black'])->id;
    $palomaId = Paloma::create([
        'palomar_id' => $palomarId,
        'anilla' => 'AN-' . time(),
        'estado_id' => $estadoId,
    ])->id;
    $categoriaId = \App\Models\Categoria::firstOrCreate(['nombre' => 'Test Categoria'])->id;
    $categoriaDocId = \App\Models\CategoriaDocumento::firstOrCreate(['nombre' => 'Test Categoria Doc'])->id;
    $itemId = Item::create(['codigo' => 'TEST-ITEM2', 'nombre' => 'Test Item 2', 'categoria_id' => $categoriaId, 'tipo_seguimiento' => 'cantidad'])->id;
    $ubicacionId = Ubicacion::create(['nombre' => 'Test Ubicacion 2', 'tipo' => 'deposito'])->id;
    $deptoId = \App\Models\Departamento::firstOrCreate(['nombre' => 'Test Depto'], ['codigo_ine' => '000'])->id;
    $rolId = \App\Models\Rol::firstOrCreate(['nombre' => 'Test Rol', 'name' => 'Test Rol'])->id;
    $permisoId = \App\Models\Permission::firstOrCreate(['name' => 'test.perm', 'description' => 'Test perm', 'model' => 'test'])->id;
    $oficinaId = \App\Models\Oficina::firstOrCreate(['nombre' => 'Test Oficina 2'])->id;
    $tipoVehiculoId = TipoVehiculo::firstOrCreate(['nombre' => 'Test TV 2'])->id;
    $tipoCombId = TipoCombustible::firstOrCreate(['nombre' => 'Test TC 2'])->id;
    $tipoLubId = TipoLubricante::firstOrCreate(['nombre' => 'Test TL 2'])->id;
    $tipoRodId = TipoRodado::firstOrCreate(['nombre' => 'Test TR 2'])->id;
    $vehiculoId = Vehiculo::create([
        'matricula' => 'TEST-MANT-' . time(),
        'marca' => 'Test',
        'modelo' => 'Test',
        'tipo_vehiculo_id' => $tipoVehiculoId,
        'unidad_id' => $unidadId,
        'tipo_combustible_id' => $tipoCombId,
        'tipo_lubricante_id' => $tipoLubId,
        'tipo_rodado_id' => $tipoRodId,
        'estado' => 'verde',
        'activo' => true,
    ])->id;

    DB::table('historial_grados')->insert([
        ['user_id' => $user->id, 'grado_id' => 1, 'tipo' => 'ascenso', 'fecha_cambio' => now(), 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('historial_estado')->insert([
        ['user_id' => $user->id, 'tipo' => 'test', 'fecha' => now(), 'motivo' => 'test', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('pases')->insert([
        ['user_id' => $user->id, 'unidad_id' => $unidadId, 'motivo' => 'test', 'fecha_desde' => now(), 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('comisiones')->insert([
        ['user_id' => $user->id, 'unidad_id' => $unidadId, 'tipo_orden' => 'servicio', 'motivo' => 'test', 'fecha_inicio' => now(), 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('documentos')->insert([
        ['categoria_documento_id' => $categoriaDocId, 'titulo' => 'test.pdf', 'archivo_path' => 'docs/test.pdf', 'nombre_original' => 'test.pdf', 'extension' => 'pdf', 'tamanio' => 1024, 'subido_por' => $user->id, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('item_unidades')->insert([
        ['item_id' => $itemId, 'responsable_id' => $user->id, 'estado' => 'activo', 'fecha_alta' => now(), 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('mantenimientos_vehiculo')->insert([
        ['vehiculo_id' => $vehiculoId, 'tipo' => 'preventivo', 'fecha' => now(), 'descripcion' => 'test', 'registrado_por' => $user->id, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('news')->insert([
        ['guard_id' => $guardId, 'user_id' => $user->id, 'type' => 'memo', 'direction' => 'entrante', 'office_id' => $oficinaId, 'number' => '001', 'text' => 'test', 'tomado_por_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('passkeys')->insert([
        ['user_id' => $user->id, 'name' => 'test-key', 'credential_id' => 'test-cred-id', 'credential' => 'test-cred', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('role_user')->insert([
        ['user_id' => $user->id, 'rol_id' => $rolId, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('user_permission')->insert([
        ['user_id' => $user->id, 'permission_id' => $permisoId, 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('direcciones')->insert([
        ['user_id' => $user->id, 'tipo' => 'particular', 'departamento_id' => $deptoId, 'calle' => 'test', 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('credenciales_civicas')->insert([
        ['user_id' => $user->id, 'departamento_id' => $deptoId, 'serie' => '001', 'numero' => '123456', 'fecha_desde' => now(), 'fecha_hasta' => now()->addYear(), 'created_at' => now(), 'updated_at' => now()],
    ]);
    DB::table('historial_palomas')->insert([
        ['paloma_id' => $palomaId, 'user_id' => $user->id, 'evento' => 'cambio_estado', 'fecha_evento' => now(), 'observaciones' => 'test'],
    ]);
    DB::table('entregas')->insert([
        ['tipo' => 'entrega', 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('movimientos')->insert([
        ['item_id' => $itemId, 'tipo' => 'entrada', 'cantidad' => 1, 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('novedades_personal')->insert([
        ['guard_id' => $guardId, 'user_id' => $user->id, 'hora' => '10:00:00', 'tipo' => 'test', 'texto' => 'test'],
    ]);

    expect(DB::table('historial_grados')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('historial_estado')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('pases')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('comisiones')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('documentos')->where('subido_por', $user->id)->count())->toBe(1);
    expect(DB::table('item_unidades')->where('responsable_id', $user->id)->count())->toBe(1);
    expect(DB::table('mantenimientos_vehiculo')->where('registrado_por', $user->id)->count())->toBe(1);
    expect(DB::table('news')->where('tomado_por_id', $user->id)->count())->toBe(1);
    expect(DB::table('passkeys')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('role_user')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('user_permission')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('direcciones')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('credenciales_civicas')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('historial_palomas')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('entregas')->where('usuario_id', $user->id)->count())->toBe(1);
    expect(DB::table('movimientos')->where('usuario_id', $user->id)->count())->toBe(1);
    expect(DB::table('novedades_personal')->where('user_id', $user->id)->count())->toBe(1);

    $user->delete();

    DB::transaction(function () use ($user) {
        DB::table('historial_palomas')->where('user_id', $user->id)->delete();
        DB::table('entregas')->where('usuario_id', $user->id)->delete();
        DB::table('movimientos')->where('usuario_id', $user->id)->delete();
        DB::table('novedades_personal')->where('user_id', $user->id)->delete();
        DB::table('historial_grados')->where('user_id', $user->id)->delete();
        DB::table('historial_estado')->where('user_id', $user->id)->delete();
        DB::table('pases')->where('user_id', $user->id)->delete();
        DB::table('comisiones')->where('user_id', $user->id)->delete();
        DB::table('documentos')->where('subido_por', $user->id)->update(['subido_por' => null]);
        DB::table('item_unidades')->where('responsable_id', $user->id)->update(['responsable_id' => null]);
        DB::table('mantenimientos_vehiculo')->where('registrado_por', $user->id)->update(['registrado_por' => null]);
        DB::table('news')->where('tomado_por_id', $user->id)->update(['tomado_por_id' => null]);
        DB::table('passkeys')->where('user_id', $user->id)->delete();
        DB::table('role_user')->where('user_id', $user->id)->delete();
        DB::table('user_permission')->where('user_id', $user->id)->delete();
        DB::table('direcciones')->where('user_id', $user->id)->delete();
        DB::table('credenciales_civicas')->where('user_id', $user->id)->delete();
        DB::table('guards')->where('captain_id', $user->id)->delete();
        DB::table('guards')->where('oficer_id', $user->id)->delete();
        $user->forceDelete();
    });

    expect(User::onlyTrashed()->where('id', $user->id)->count())->toBe(0);
    expect(User::withTrashed()->where('id', $user->id)->count())->toBe(0);
    expect(DB::table('historial_grados')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('historial_estado')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('pases')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('comisiones')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('documentos')->where('subido_por', $user->id)->count())->toBe(0);
    expect(DB::table('item_unidades')->where('responsable_id', $user->id)->count())->toBe(0);
    expect(DB::table('mantenimientos_vehiculo')->where('registrado_por', $user->id)->count())->toBe(0);
    expect(DB::table('news')->where('tomado_por_id', $user->id)->count())->toBe(0);
    expect(DB::table('passkeys')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('role_user')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('user_permission')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('direcciones')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('credenciales_civicas')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('historial_palomas')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('entregas')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('movimientos')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('novedades_personal')->where('user_id', $user->id)->count())->toBe(0);
});

// ── Tests para flujos "incompleto" (perfil_completo_at IS NULL) ──

test('destroy incompleto clears FK RESTRICT tables', function () {
    if (DB::getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    $user = User::factory()->create([
        'name' => 'Test Incompleto FK',
        'email' => 'test-incompleto-fk-' . time() . '@test.com',
        'perfil_completo_at' => null,
    ]);

    $guardId = Guard::create(['date' => now()->format('Y-m-d'), 'captain_id' => $user->id, 'oficer_id' => $user->id, 'status' => 'open'])->id;
    $palomarId = Palomar::create(['nombre' => 'Test Palomar Incompleto'])->id;
    $estadoId = EstadoPaloma::firstOrCreate(['nombre' => 'Test Estado Incompleto'], ['color' => 'black'])->id;
    $palomaId = Paloma::create([
        'palomar_id' => $palomarId,
        'anilla' => 'AN-' . time(),
        'estado_id' => $estadoId,
    ])->id;
    $categoriaId = \App\Models\Categoria::firstOrCreate(['nombre' => 'Test Categoria Incompleto'])->id;
    $itemId = Item::create(['codigo' => 'TEST-ITEM-INC', 'nombre' => 'Test Item Inc', 'categoria_id' => $categoriaId, 'tipo_seguimiento' => 'cantidad'])->id;
    $ubicacionId = Ubicacion::create(['nombre' => 'Test Ubicacion Inc', 'tipo' => 'deposito'])->id;

    // Insert data in the 4 FK RESTRICT tables (los únicos que limpia destroyIncompleto)
    DB::table('historial_palomas')->insert([
        ['paloma_id' => $palomaId, 'user_id' => $user->id, 'evento' => 'cambio_estado', 'fecha_evento' => now(), 'observaciones' => 'test'],
    ]);
    DB::table('entregas')->insert([
        ['tipo' => 'entrega', 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('movimientos')->insert([
        ['item_id' => $itemId, 'tipo' => 'entrada', 'cantidad' => 1, 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('novedades_personal')->insert([
        ['guard_id' => $guardId, 'user_id' => $user->id, 'hora' => '10:00:00', 'tipo' => 'test', 'texto' => 'test'],
    ]);

    // Verify data exists
    expect(DB::table('historial_palomas')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('entregas')->where('usuario_id', $user->id)->count())->toBe(1);
    expect(DB::table('movimientos')->where('usuario_id', $user->id)->count())->toBe(1);
    expect(DB::table('novedades_personal')->where('user_id', $user->id)->count())->toBe(1);

    // Simulate destroyIncompleto: direct force delete (no soft delete step)
    DB::transaction(function () use ($user) {
        // FK RESTRICT / NO ACTION
        DB::table('historial_palomas')->where('user_id', $user->id)->delete();
        DB::table('entregas')->where('usuario_id', $user->id)->delete();
        DB::table('movimientos')->where('usuario_id', $user->id)->delete();
        DB::table('novedades_personal')->where('user_id', $user->id)->delete();

        // FK CASCADE / SET NULL
        DB::table('historial_grados')->where('user_id', $user->id)->delete();
        DB::table('historial_estado')->where('user_id', $user->id)->delete();
        DB::table('pases')->where('user_id', $user->id)->delete();
        DB::table('comisiones')->where('user_id', $user->id)->delete();

        $user->forceDelete();
    });

    // Verify everything was deleted
    expect(User::withTrashed()->where('id', $user->id)->count())->toBe(0);
    expect(DB::table('historial_palomas')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('entregas')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('movimientos')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('novedades_personal')->where('user_id', $user->id)->count())->toBe(0);
});

test('ejecutar eliminacion permanente incompleto desde papelera limpia FK RESTRICT', function () {
    if (DB::getDriverName() === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON');
    }

    $user = User::factory()->create([
        'name' => 'Test Incompleto Trash FK',
        'email' => 'test-incompleto-trash-fk-' . time() . '@test.com',
        'perfil_completo_at' => null,
    ]);

    $guardId = Guard::create(['date' => now()->format('Y-m-d'), 'captain_id' => $user->id, 'oficer_id' => $user->id, 'status' => 'open'])->id;
    $palomarId = Palomar::create(['nombre' => 'Test Palomar Trash'])->id;
    $estadoId = EstadoPaloma::firstOrCreate(['nombre' => 'Test Estado Trash'], ['color' => 'black'])->id;
    $palomaId = Paloma::create([
        'palomar_id' => $palomarId,
        'anilla' => 'AN-' . time(),
        'estado_id' => $estadoId,
    ])->id;
    $categoriaId = \App\Models\Categoria::firstOrCreate(['nombre' => 'Test Categoria Trash'])->id;
    $itemId = Item::create(['codigo' => 'TEST-ITEM-TRASH', 'nombre' => 'Test Item Trash', 'categoria_id' => $categoriaId, 'tipo_seguimiento' => 'cantidad'])->id;
    $ubicacionId = Ubicacion::create(['nombre' => 'Test Ubicacion Trash', 'tipo' => 'deposito'])->id;

    // Insert data in FK RESTRICT tables
    DB::table('historial_palomas')->insert([
        ['paloma_id' => $palomaId, 'user_id' => $user->id, 'evento' => 'cambio_estado', 'fecha_evento' => now(), 'observaciones' => 'test'],
    ]);
    DB::table('entregas')->insert([
        ['tipo' => 'entrega', 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('movimientos')->insert([
        ['item_id' => $itemId, 'tipo' => 'entrada', 'cantidad' => 1, 'ubicacion_origen_id' => $ubicacionId, 'ubicacion_destino_id' => $ubicacionId, 'usuario_id' => $user->id, 'motivo' => 'test'],
    ]);
    DB::table('novedades_personal')->insert([
        ['guard_id' => $guardId, 'user_id' => $user->id, 'hora' => '10:00:00', 'tipo' => 'test', 'texto' => 'test'],
    ]);

    // Soft delete (simula mover a papelera)
    $user->delete();

    expect(User::onlyTrashed()->where('id', $user->id)->count())->toBe(1);

    // Simulate ejecutarEliminacionPermanenteIncompleto: force delete from trash
    DB::transaction(function () use ($user) {
        // FK RESTRICT / NO ACTION
        DB::table('historial_palomas')->where('user_id', $user->id)->delete();
        DB::table('entregas')->where('usuario_id', $user->id)->delete();
        DB::table('movimientos')->where('usuario_id', $user->id)->delete();
        DB::table('novedades_personal')->where('user_id', $user->id)->delete();

        // FK CASCADE / SET NULL
        DB::table('historial_grados')->where('user_id', $user->id)->delete();
        DB::table('historial_estado')->where('user_id', $user->id)->delete();
        DB::table('pases')->where('user_id', $user->id)->delete();
        DB::table('comisiones')->where('user_id', $user->id)->delete();

        $user->forceDelete();
    });

    // Verify everything was deleted
    expect(User::onlyTrashed()->where('id', $user->id)->count())->toBe(0);
    expect(User::withTrashed()->where('id', $user->id)->count())->toBe(0);
    expect(DB::table('historial_palomas')->where('user_id', $user->id)->count())->toBe(0);
    expect(DB::table('entregas')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('movimientos')->where('usuario_id', $user->id)->count())->toBe(0);
    expect(DB::table('novedades_personal')->where('user_id', $user->id)->count())->toBe(0);
});
