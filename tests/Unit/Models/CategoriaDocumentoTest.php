<?php

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('categoria_documento can be created via factory', function () {
    $cat = CategoriaDocumento::factory()->create();

    expect($cat)->toBeInstanceOf(CategoriaDocumento::class);
    expect($cat->nombre)->not->toBeEmpty();
});

test('categoria_documento table is categorias_documentos', function () {
    expect((new CategoriaDocumento)->getTable())->toBe('categorias_documentos');
});

test('categoria_documento uses LogsActivity trait', function () {
    $cat = CategoriaDocumento::factory()->create();

    expect(method_exists($cat, 'getActivitylogOptions'))->toBeTrue();
});
