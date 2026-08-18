<?php

use App\Models\TipoCombustible;
use App\Models\TipoLubricante;
use App\Models\TipoRodado;
use App\Models\TipoVehiculo;
use App\Models\Unidad;
use App\Models\Vehiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    // Pre-create related records to avoid unique constraint violations
    // when the factory creates multiple vehicles in one test
    if (TipoVehiculo::count() === 0) {
        TipoVehiculo::factory()->create();
    }
    if (Unidad::count() === 0) {
        Unidad::factory()->create();
    }
    if (TipoCombustible::count() === 0) {
        TipoCombustible::factory()->create();
    }
    if (TipoLubricante::count() === 0) {
        TipoLubricante::factory()->create();
    }
    if (TipoRodado::count() === 0) {
        TipoRodado::factory()->create();
    }
});

test('vehiculo can be created via factory', function () {
    $vehiculo = Vehiculo::factory()->create();

    expect($vehiculo)->toBeInstanceOf(Vehiculo::class);
    expect($vehiculo->matricula)->not->toBeEmpty();
    expect($vehiculo->activo)->toBeTrue();
});

test('vehiculo table is vehiculos', function () {
    expect((new Vehiculo)->getTable())->toBe('vehiculos');
});

test('vehiculo uses SoftDeletes trait', function () {
    $vehiculo = Vehiculo::factory()->create();
    $vehiculoId = $vehiculo->id;

    $vehiculo->delete();

    expect(Vehiculo::find($vehiculoId))->toBeNull();
    expect(Vehiculo::withTrashed()->find($vehiculoId))->not->toBeNull();

    $vehiculo->restore();
    expect(Vehiculo::find($vehiculoId))->not->toBeNull();
});

test('getNombreCompletoAttribute returns formatted name with marca and modelo', function () {
    $vehiculo = Vehiculo::factory()->create([
        'matricula' => 'AA-123',
        'marca' => 'Ford',
        'modelo' => 'Furgon',
    ]);

    expect($vehiculo->nombreCompleto)->toBe('AA-123 - Ford Furgon');
});

test('getNombreCompletoAttribute falls back to descripcion when marca and modelo are empty', function () {
    $vehiculo = Vehiculo::factory()->create([
        'matricula' => 'BB-456',
        'marca' => '',
        'modelo' => '',
        'descripcion' => 'Vehiculo de apoyo',
    ]);

    expect($vehiculo->nombreCompleto)->toBe('BB-456 - Vehiculo de apoyo');
});

test('getEstadoLabelAttribute returns correct labels', function () {
    $verde = Vehiculo::factory()->create(['estado' => 'verde']);
    $amarillo = Vehiculo::factory()->create(['estado' => 'amarillo']);
    $rojo = Vehiculo::factory()->create(['estado' => 'rojo']);
    $negro = Vehiculo::factory()->create(['estado' => 'negro']);
    $otro = Vehiculo::factory()->create(['estado' => 'desconocido']);

    expect($verde->estadoLabel)->toBe('OK');
    expect($amarillo->estadoLabel)->toBe('Observación');
    expect($rojo->estadoLabel)->toBe('Fuera de servicio');
    expect($negro->estadoLabel)->toBe('Dado de baja');
    expect($otro->estadoLabel)->toBe('Desconocido');
});

test('getEstadoBadgeClassAttribute returns correct badge classes', function () {
    $verde = Vehiculo::factory()->create(['estado' => 'verde']);
    $amarillo = Vehiculo::factory()->create(['estado' => 'amarillo']);
    $rojo = Vehiculo::factory()->create(['estado' => 'rojo']);
    $negro = Vehiculo::factory()->create(['estado' => 'negro']);
    $otro = Vehiculo::factory()->create(['estado' => 'desconocido']);

    expect($verde->estadoBadgeClass)->toBe('badge badge-success');
    expect($amarillo->estadoBadgeClass)->toBe('badge badge-warning');
    expect($rojo->estadoBadgeClass)->toBe('badge badge-danger');
    expect($negro->estadoBadgeClass)->toBe('badge badge-dark');
    expect($otro->estadoBadgeClass)->toBe('badge badge-secondary');
});

test('carpetaActas returns sanitized path', function () {
    $vehiculo = Vehiculo::factory()->create(['matricula' => 'AA-123']);

    expect($vehiculo->carpetaActas())->toBe('actas/AA_123');
});

test('carpetaActas handles special characters in matricula', function () {
    $vehiculo = Vehiculo::factory()->create(['matricula' => 'AA--123__B']);

    expect($vehiculo->carpetaActas())->toBe('actas/AA_123_B');
});

test('carpetaActas uppercases matricula', function () {
    $vehiculo = Vehiculo::factory()->create(['matricula' => 'AA-123']);

    expect($vehiculo->carpetaActas())->toBe('actas/AA_123');
});

test('forceDelete removes actas folder from storage', function () {
    $vehiculo = Vehiculo::factory()->create(['matricula' => 'ZZ-999']);
    $carpeta = $vehiculo->carpetaActas();

    // Simulate actas folder existing
    Storage::disk('public')->makeDirectory($carpeta);
    Storage::disk('public')->put("{$carpeta}/test-acta.pdf", 'content');
    expect(Storage::disk('public')->exists("{$carpeta}/test-acta.pdf"))->toBeTrue();

    // Force delete triggers the forceDeleting event
    $vehiculo->forceDelete();

    expect(Storage::disk('public')->exists($carpeta))->toBeFalse();
});

test('forceDelete does not remove actas folder if it does not exist', function () {
    $vehiculo = Vehiculo::factory()->create(['matricula' => 'YY-888']);
    $carpeta = $vehiculo->carpetaActas();

    // No folder exists - should not throw
    expect(fn () => $vehiculo->forceDelete())->not->toThrow(InvalidArgumentException::class);
});

test('soft delete preserves actas folder', function () {
    $vehiculo = Vehiculo::factory()->create(['matricula' => 'XX-777']);
    $carpeta = $vehiculo->carpetaActas();

    Storage::disk('public')->makeDirectory($carpeta);
    Storage::disk('public')->put("{$carpeta}/test-acta.pdf", 'content');

    // Soft delete should NOT trigger the forceDeleting event
    $vehiculo->delete();

    // Folder should still exist after soft delete
    expect(Storage::disk('public')->exists($carpeta))->toBeTrue();
    expect(Storage::disk('public')->exists("{$carpeta}/test-acta.pdf"))->toBeTrue();
});

test('vehiculo relationships return correct types', function () {
    $vehiculo = Vehiculo::factory()->create();

    expect($vehiculo->unidad)->toBeInstanceOf(Unidad::class);
    expect($vehiculo->tipoVehiculo)->toBeInstanceOf(TipoVehiculo::class);
    expect($vehiculo->tipoCombustible)->toBeInstanceOf(TipoCombustible::class);
    expect($vehiculo->tipoLubricante)->toBeInstanceOf(TipoLubricante::class);
    expect($vehiculo->tipoRodado)->toBeInstanceOf(TipoRodado::class);
});

test('vehiculo mantenimientos ordered by date desc', function () {
    $vehiculo = Vehiculo::factory()->create();
    $older = \App\Models\MantenimientoVehiculo::create([
        'vehiculo_id' => $vehiculo->id,
        'tipo' => 'preventivo',
        'fecha' => now()->subDays(10),
        'descripcion' => 'Old maintenance',
        'registrado_por' => 1,
    ]);
    $newer = \App\Models\MantenimientoVehiculo::create([
        'vehiculo_id' => $vehiculo->id,
        'tipo' => 'correctivo',
        'fecha' => now(),
        'descripcion' => 'New maintenance',
        'registrado_por' => 1,
    ]);

    $mantenimientos = $vehiculo->mantenimientos;
    expect($mantenimientos->first()->id)->toBe($newer->id);
    expect($mantenimientos->last()->id)->toBe($older->id);
});

test('vehiculo activo boolean is cast correctly', function () {
    $activo = Vehiculo::factory()->create(['activo' => true]);
    $inactivo = Vehiculo::factory()->create(['activo' => false]);

    expect($activo->activo)->toBeTrue();
    expect($inactivo->activo)->toBeFalse();
});

test('vehiculo sin_cuentakilometros boolean is cast correctly', function () {
    $with = Vehiculo::factory()->create(['sin_cuentakilometros' => true]);
    $without = Vehiculo::factory()->create(['sin_cuentakilometros' => false]);

    expect($with->sin_cuentakilometros)->toBeTrue();
    expect($without->sin_cuentakilometros)->toBeFalse();
});

test('vehiculo consumo_litros_por_km is decimal cast', function () {
    $vehiculo = Vehiculo::factory()->create(['consumo_litros_por_km' => 0.4567]);

    expect($vehiculo->consumo_litros_por_km)->toBe('0.4567');
});
