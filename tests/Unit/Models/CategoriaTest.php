<?php

use App\Models\Categoria;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('categoria can be created via factory', function () {
    $categoria = Categoria::factory()->create();

    expect($categoria)->toBeInstanceOf(Categoria::class);
    expect($categoria->nombre)->not->toBeEmpty();
    expect($categoria->slug)->not->toBeEmpty();
});

test('categoria auto-generates slug from nombre on create', function () {
    $categoria = Categoria::factory()->create(['nombre' => 'Mi Categoria']);

    expect($categoria->slug)->toBe('mi-categoria');
});

test('categoria generates unique slug with suffix when collision', function () {
    Categoria::factory()->create(['nombre' => 'Categoria']);
    $cat2 = Categoria::factory()->create(['nombre' => 'Categoria']);
    $cat3 = Categoria::factory()->create(['nombre' => 'Categoria']);

    expect($cat2->slug)->toBe('categoria-2');
    expect($cat3->slug)->toBe('categoria-3');
});

test('categoria updates slug when nombre changes and slug not manually set', function () {
    $categoria = Categoria::factory()->create(['nombre' => 'Original']);
    expect($categoria->slug)->toBe('original');

    $categoria->update(['nombre' => 'Modificado']);

    expect($categoria->fresh()->slug)->toBe('modificado');
});

test('categoria does not update slug when nombre changes but slug was manually set', function () {
    $categoria = Categoria::factory()->create(['nombre' => 'Original', 'slug' => 'manual-slug']);
    expect($categoria->slug)->toBe('manual-slug');

    // Set the slug attribute before update to prevent regeneration
    $categoria->slug = 'manual-slug';
    $categoria->update(['nombre' => 'Modificado']);

    expect($categoria->fresh()->slug)->toBe('manual-slug');
});

test('categoria padre-hijas self-referencing relationship', function () {
    $padre = Categoria::factory()->create(['nombre' => 'Padre']);
    $hija1 = Categoria::factory()->hija($padre->id)->create(['nombre' => 'Hija 1']);
    $hija2 = Categoria::factory()->hija($padre->id)->create(['nombre' => 'Hija 2']);

    expect($padre->hijas)->toHaveCount(2);
    expect($padre->hijas->pluck('id')->toArray())->toContain($hija1->id);
    expect($padre->hijas->pluck('id')->toArray())->toContain($hija2->id);

    expect($hija1->padre)->not->toBeNull();
    expect($hija1->padre->id)->toBe($padre->id);
});

test('categoria hijasRecursivas returns nested children', function () {
    $padre = Categoria::factory()->create(['nombre' => 'Padre']);
    $hija1 = Categoria::factory()->hija($padre->id)->create(['nombre' => 'Hija 1']);
    $neta = Categoria::factory()->hija($hija1->id)->create(['nombre' => 'Neta']);

    // Refresh and load recursively (load() does recursive loading, with() doesn't)
    $padre = Categoria::find($padre->id);
    $padre->load('hijasRecursivas');

    expect($padre->hijasRecursivas)->toHaveCount(2);
});

test('categoria items relationship', function () {
    $categoria = Categoria::factory()->create();
    // Use make() to get an Item instance without auto-creating a new categoria
    $item1 = Item::factory()->make(['categoria_id' => $categoria->id]);
    $item1->save();
    $item2 = Item::factory()->make(['categoria_id' => $categoria->id]);
    $item2->save();

    expect($categoria->fresh()->items)->toHaveCount(2);
});

test('categoria uses HasFactory trait', function () {
    $categoria = Categoria::factory()->make();

    expect($categoria)->toBeInstanceOf(Categoria::class);
});
