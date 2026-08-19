<?php

use Illuminate\Support\Facades\DB;

test('la conexion de test es sqlite en memoria, no mysql', function () {
    expect(config('database.default'))->toBe('sqlite');
    expect(DB::connection()->getConfig('database'))->toBe(':memory:');
});
