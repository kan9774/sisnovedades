<?php

use App\Models\News;
use App\Models\Guard;
use App\Models\User;
use App\Models\Organismo;
use App\Models\Oficina;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Tests de modelo News (post-migración, con RefreshDatabase)
|--------------------------------------------------------------------------
| La migración ya corrió, destino es array/texto. Los tests verifican
| que el modelo maneja correctamente los valores JSON.
*/

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->guard = Guard::factory()->create(['status' => 'open']);
    $this->organismo = Organismo::factory()->create();
    $this->oficina = Oficina::factory()->create();
});

/*
|--------------------------------------------------------------------------
| Bug 3 — scopeUrgentes con whereIn
|--------------------------------------------------------------------------
*/

test('scopeUrgentes devuelve solo Urgente y Destello', function () {
    News::create([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'number'      => '008',
        'text'        => 'Urgente',
        'clasification' => 'Urgente',
        'office_id'   => $this->oficina->id,
    ]);

    News::create([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'number'      => '009',
        'text'        => 'Destello',
        'clasification' => 'Destello',
        'office_id'   => $this->oficina->id,
    ]);

    News::create([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'number'      => '010',
        'text'        => 'Rutinario',
        'clasification' => 'Rutinario',
        'office_id'   => $this->oficina->id,
    ]);

    News::create([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'number'      => '011',
        'text'        => 'Prioritario',
        'clasification' => 'Prioritario',
        'office_id'   => $this->oficina->id,
    ]);

    $urgentes = News::urgentes()->get();

    expect($urgentes)->toHaveCount(2);
    expect($urgentes->pluck('clasification')->toArray())->toContain('Urgente', 'Destello');
    expect($urgentes->pluck('clasification')->toArray())->not->toContain('Rutinario', 'Prioritario');
});

test('scopeUrgentes retorna vacio cuando no hay clasificaciones urgentes', function () {
    News::create([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'number'      => '012',
        'text'        => 'Rutinario',
        'clasification' => 'Rutinario',
        'office_id'   => $this->oficina->id,
    ]);

    expect(News::urgentes()->count())->toBe(0);
});

test('esUrgente helper funciona correctamente', function () {
    $urgente = News::create([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'number'      => '013',
        'text'        => 'Urgente',
        'clasification' => 'Urgente',
        'office_id'   => $this->oficina->id,
    ]);

    $rutinario = News::create([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'number'      => '014',
        'text'        => 'Rutinario',
        'clasification' => 'Rutinario',
        'office_id'   => $this->oficina->id,
    ]);

    expect($urgente->esUrgente())->toBeTrue();
    expect($rutinario->esUrgente())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Destinos helpers (post-migración)
|--------------------------------------------------------------------------
*/

test('destinosFormateados maneja array de destinos', function () {
    // Insertar JSON array directamente en la DB (simula dato post-migración)
    DB::table('news')->insert([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Expedido',
        'destino'     => json_encode(['Batallon 1', 'Batallon 2', 'Batallon 3']),
        'number'      => '005',
        'text'        => 'Prueba',
        'clasification' => 'Rutinario',
        'office_id'   => $this->oficina->id,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    $news = News::first();
    expect($news->destinosFormateados())->toBe('Batallon 1, Batallon 2, Batallon 3');
});

test('destinosFormateados retorna guion cuando destino es null', function () {
    DB::table('news')->insert([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Recibido',
        'destino'     => null,
        'number'      => '006',
        'text'        => 'Prueba',
        'clasification' => 'Rutinario',
        'office_id'   => $this->oficina->id,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    $news = News::first();
    expect($news->destinosFormateados())->toBe('—');
});

test('tieneDestino verifica existencia de destino', function () {
    DB::table('news')->insert([
        'guard_id'    => $this->guard->id,
        'user_id'     => $this->user->id,
        'type'        => 'Radio',
        'direction'   => 'Expedido',
        'destino'     => json_encode(['Batallon 1', 'Batallon 2']),
        'number'      => '007',
        'text'        => 'Prueba',
        'clasification' => 'Rutinario',
        'office_id'   => $this->oficina->id,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    $news = News::first();
    expect($news->tieneDestino('Batallon 1'))->toBeTrue();
    expect($news->tieneDestino('Batallon 2'))->toBeTrue();
    expect($news->tieneDestino('Batallon 3'))->toBeFalse();
});


