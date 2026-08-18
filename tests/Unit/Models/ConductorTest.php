<?php

use App\Models\Conductor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('conductor can be created via factory', function () {
    $conductor = Conductor::factory()->create();

    expect($conductor)->toBeInstanceOf(Conductor::class);
    expect($conductor->activo)->toBeTrue();
});

test('conductor table is conductores', function () {
    expect((new Conductor)->getTable())->toBe('conductores');
});

test('conductor uses SoftDeletes trait', function () {
    $conductor = Conductor::factory()->create();
    $id = $conductor->id;

    $conductor->delete();

    expect(Conductor::find($id))->toBeNull();
    expect(Conductor::withTrashed()->find($id))->not->toBeNull();

    $conductor->restore();
    expect(Conductor::find($id))->not->toBeNull();
});

test('getNombreCompletoAttribute returns full name with all parts', function () {
    $conductor = Conductor::factory()->create([
        'grado' => 'Sargento',
        'primer_nombre' => 'Juan',
        'segundo_nombre' => 'Carlos',
        'primer_apellido' => 'Garcia',
        'segundo_apellido' => 'Lopez',
    ]);

    expect($conductor->nombreCompleto)->toBe('Sargento Juan Carlos Garcia Lopez');
});

test('getNombreCompletoAttribute skips empty parts', function () {
    $conductor = Conductor::factory()->create([
        'grado' => 'Cabo',
        'primer_nombre' => 'Maria',
        'segundo_nombre' => null,
        'primer_apellido' => 'Rodriguez',
        'segundo_apellido' => null,
    ]);

    expect($conductor->nombreCompleto)->toBe('Cabo Maria Rodriguez');
});

test('getNombreCortoAttribute returns grado and initials', function () {
    $conductor = Conductor::factory()->create([
        'grado' => 'Teniente',
        'primer_nombre' => 'Pedro',
        'primer_apellido' => 'Martinez',
    ]);

    expect($conductor->nombreCorto)->toBe('Teniente PM.');
});

test('getNombreVisibleAttribute returns grado and first name + last name', function () {
    $conductor = Conductor::factory()->create([
        'grado' => 'Sargento',
        'primer_nombre' => 'Ana',
        'segundo_nombre' => 'Maria',
        'primer_apellido' => 'Fernandez',
    ]);

    expect($conductor->nombreVisible)->toBe('Sargento Ana Fernandez');
});

test('getLicenciaVigenteAttribute returns true when not expired', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_licencia' => now()->addMonths(6),
    ]);

    expect($conductor->licenciaVigente)->toBeTrue();
});

test('getLicenciaVigenteAttribute returns false when expired', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_licencia' => now()->subMonths(3),
    ]);

    expect($conductor->licenciaVigente)->toBeFalse();
});

test('getLicenciaVigenteAttribute returns true when expiring today', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_licencia' => today(),
    ]);

    expect($conductor->licenciaVigente)->toBeTrue();
});

test('getCarneSaludVigenteAttribute returns true when valid', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_carne_salud' => now()->addMonths(3),
    ]);

    expect($conductor->carneSaludVigente)->toBeTrue();
});

test('getCarneSaludVigenteAttribute returns false when null', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_carne_salud' => null,
    ]);

    expect($conductor->carneSaludVigente)->toBeFalse();
});

test('getCarneSaludVigenteAttribute returns false when expired', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_carne_salud' => now()->subMonths(1),
    ]);

    expect($conductor->carneSaludVigente)->toBeFalse();
});

test('getCarneHabilitanteVigenteAttribute returns true when valid', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_carne_habilitante' => now()->addMonths(6),
    ]);

    expect($conductor->carneHabilitanteVigente)->toBeTrue();
});

test('getCarneHabilitanteVigenteAttribute returns false when null', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_carne_habilitante' => null,
    ]);

    expect($conductor->carneHabilitanteVigente)->toBeFalse();
});

test('conductor date fields are cast correctly', function () {
    $conductor = Conductor::factory()->create([
        'fecha_vencimiento_licencia' => '2026-12-31',
        'fecha_vencimiento_carne_salud' => '2026-06-30',
        'fecha_vencimiento_carne_habilitante' => '2026-09-15',
    ]);

    expect($conductor->fecha_vencimiento_licencia)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    expect($conductor->fecha_vencimiento_carne_salud)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    expect($conductor->fecha_vencimiento_carne_habilitante)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

test('conductor activo boolean is cast correctly', function () {
    $activo = Conductor::factory()->create(['activo' => true]);
    $inactivo = Conductor::factory()->create(['activo' => false]);

    expect($activo->activo)->toBeTrue();
    expect($inactivo->activo)->toBeFalse();
});

test('conductor salidasVehiculos relationship', function () {
    $conductor = Conductor::factory()->create();
    $guardia = \App\Models\Guard::factory()->create();
    $vehiculo = \App\Models\Vehiculo::factory()->create();
    $salida1 = \App\Models\SalidaVehiculo::create([
        'guardia_id' => $guardia->id,
        'vehiculo_id' => $vehiculo->id,
        'conductor_id' => $conductor->id,
        'hora_sale' => '10:00:00',
        'comision' => 'Test',
    ]);

    expect($conductor->salidasVehiculos)->toHaveCount(1);
    expect($conductor->salidasVehiculos->first()->id)->toBe($salida1->id);
});
