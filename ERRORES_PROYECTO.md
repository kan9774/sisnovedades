# Errores Encontrados - Proyecto Novedades

> **Nota:** Solo se listan los errores. No se modificó ningún archivo.

---

## 🔴 Errores Críticos (rompen funcionalidad)

### 1. Import incorrecto en `User.php` — SoftDeletes mal escrito
**Archivo:** `app/Models/User.php` — línea ~22

```php
use Illuminate\database\Eloquent\SoftDeletes;  // ❌ 'd' minúscula
```

**Debería ser:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;  // ✅ 'D' mayúscula
```

Esto causará un `Class not found` al instanciar el modelo User.

---

### 2. Trait `softDeletes` en minúsculas en User.php
**Archivo:** `app/Models/User.php` — línea ~35

```php
use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, softDeletes, LogsActivity;
//                                                                           ^ minúscula
```

**Debería ser:**
```php
use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, SoftDeletes, LogsActivity;
```

PHP es case-sensitive para los traits. Esto causará un error fatal.

---

### 3. `AppServiceProvider::register()` sobrescribe `path.public` incorrectamente
**Archivo:** `app/Providers/AppServiceProvider.php` — línea ~37

```php
$this->app->bind('path.public', function () {
    return storage_path() . '/public_html';  // ❌ No es el path público correcto
});
```

Esto hace que `public_path()` apunte a `storage/app/public_html` en vez de `public/`, rompiendo la entrega de assets estáticos (JS, CSS, imágenes, storage link).

---

### 4. `Guard::esMiembro()` usa relación sin eager-load → N+1 / datos incorrectos
**Archivo:** `app/Models/Guard.php` — línea ~93

```php
public function esMiembro(?User $user): bool
{
    return $this->captain_id === $user->id
        || $this->oficer_id === $user->id
        || $this->escribiente->contains('id', $user->id);  // ❌ lazy-load
}
```

Si la relación `escribiente` no fue cargada eager, `$this->escribiente` devuelve una instancia de `HasManyThrough` o similar, no una colección. Esto fallará silenciosamente o causará múltiples queries.

**Se usa en:** `NovedadPersonalController`, `NovedadRanchoController`, `AutorizaOperacionGuardia` trait.

---

### 5. Rutas duplicadas para `guardias` — resource + custom routes
**Archivo:** `routes/web.php` — líneas ~104-113

```php
Route::get('/guardias/hoy', [GuardiaController::class, 'hoy'])->name('guardias.hoy');
Route::get('/guardias/trashed', [GuardiaController::class, 'trashed'])->name('guardias.trashed');
Route::resource('guardias', GuardiaController::class)->only(['index', 'create', 'store', 'show']);
Route::post('/guardias/{guardia}/cerrar', ...);
Route::post('/guardias/{guardia}/reactivar', ...);
Route::get('/guardias/{guardia}/pdf', ...);
Route::post('/guardias/{id}/restore', ...);
Route::delete('/guardias/{id}/force-delete', ...);
Route::delete('/guardias/{guardia}', ...);
```

El `Route::resource` ya genera `destroy` (DELETE). La línea `Route::delete('/guardias/{guardia}', ...)` es redundante y puede causar conflicto de rutas. Además, el resource define `edit` y `update` como rutas pero el controller **no tiene** esos métodos, lo que causará 404.

---

### 6. `SalidaVehiculo::calcularKmsYlitros()` puede fallar con valores null
**Archivo:** `app/Models/SalidaVehiculo.php` — línea ~59

```php
public function calcularKmsYlitros()
{
    $vehiculo = $this->vehiculo;  // ❌ puede ser null (con withTrashed)

    if (!$vehiculo->sin_cuentakilometros && $this->kms_sale && $this->kms_entra) {
        $this->kms_recorridos = $this->kms_entra - $this->kms_sale;
    }
    // ...
    if ($vehiculo->consumo_litros_por_km && $this->kms_recorridos) {
        $this->litros = $this->kms_recorridos * $vehiculo->consumo_litros_por_km;
        $this->consumo_usado = $vehiculo->consumo_litros_por_km;
    }
}
```

Si `$vehiculo` es null o `$this->kms_sale`/`$this->kms_entra` son null, puede haber errores de tipo o resultados inesperados.

---

## 🟡 Errores Moderados (pueden causar bugs)

### 7. `NovedadesController::store()` valida `time` como string pero se cast a datetime
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~52

```php
'time' => 'required|date_format:H:i',  // Valida como string
```

**Pero en el modelo:**
```php
'time' => 'datetime:H:i',  // Se cast como Carbon
```

Al crear con `News::create(['time' => '08:30'])`, Laravel intentará parsear `'08:30'` como datetime y puede fallar o dar resultados inesperados. Debería usarse `date_format` o pasar un Carbon.

---

### 8. `ConductorController::show()` carga relación inexistente
**Archivo:** `app/Http/Controllers/ConductorController.php` — línea ~69

```php
$conductor->load(['novedadesVehiculos' => function($query) {
    $query->latest()->limit(10);
}]);
```

**El modelo `Conductor` no tiene la relación `novedadesVehiculos`.** Esto causará un error al intentar cargar la relación.

---

### 9. `GuardiaPdfGenerator::generar()` usa `loadMissing` en vez de `load`
**Archivo:** `app/Support/GuardiaPdfGenerator.php` — línea ~16

```php
$guardia->loadMissing([...]);  // Solo carga si NO están cargados
```

Si las relaciones ya fueron cargadas (pero incompletas), `loadMissing` no las refresca, y el PDF podría renderizar datos desactualizados. Debería usarse `load()` para garantizar que todo esté presente.

---

### 10. `NovedadPersonalController` y `NovedadRanchoController` sin policies registradas
**Archivos:** `app/Http/Controllers/NovedadPersonalController.php`, `app/Http/Controllers/NovedadRanchoController.php`

Estos controladores usan el trait `AutorizaOperacionGuardia` que hace `abort_if`/`abort_unless` manualmente, pero no hay policies registradas en `AppServiceProvider` para los modelos `NovedadPersonal` y `NovedadRancho`. Si en el futuro se usan `@can` en las vistas, fallarán.

---

### 11. `NovedadRancho` model tiene `menu` en fillable, pero controller envía `menu_*` separados
**Archivo:** `app/Models/NovedadRancho.php`

```php
protected $fillable = [..., 'menu'];  // ❌ 'menu' no se llena
```

**En `NovedadRanchoController::update()`:**
```php
RanchoMenu::updateOrCreate(['guard_id' => $guardia->id], $menus->toArray());
```

Los datos de menú van a `RanchoMenu`, no a `NovedadRancho`. El campo `menu` en `NovedadRancho` nunca se llena, lo que sugiere una inconsistencia de diseño o un campo huérfano.

---

### 12. Migration `novedades_personal` down usa nombre incorrecto
**Archivo:** `database/migrations/2026_07_09_195529_create_novedades_personal_table.php` — línea ~22

```php
Schema::dropIfExists('novedades_personals');  // ❌ nombre incorrecto
```

**Debería ser:**
```php
Schema::dropIfExists('novedades_personal');  // ✅ mismo nombre que en up()
```

---

### 13. Migration `novedades_rancho` down usa nombre incorrecto
**Archivo:** `database/migrations/2026_07_09_195659_create_novedades_rancho_table.php` — línea ~22

```php
Schema::dropIfExists('novedades_ranchos');  // ❌ nombre incorrecto
```

**Debería ser:**
```php
Schema::dropIfExists('novedades_rancho');  // ✅ mismo nombre que en up()
```

---

### 14. `Guard::Hoy()` en `GuardiaController` con parámetros incorrectos
**Archivo:** `app/Http/Controllers/GuardiaController.php` — línea ~117

```php
public function Hoy()
{
    $guardia = Guard::Hoy('date', now()->toDateString())->first();  // ❌ parámetros extra
    // ...
}
```

El scope `scopeHoy` en el modelo acepta 0 parámetros:
```php
public function scopeHoy($query) {
    return $query->whereDate('date', today());
}
```

Pasar `'date'` y `now()->toDateString()` como argumentos causará error. Debería ser `Guard::Hoy()->first()`.

---

### 15. `NovedadesController::store()` y `update()` duplican lógica de validación
**Archivo:** `app/Http/Controllers/NovedadesController.php`

La validación de `archivo` se hace con `$request->validate()` separado de la validación principal. Si el archivo no pasa la validación, el primer `validate()` ya lanza error, pero el segundo `validate()` nunca se ejecuta. Esto no es un bug funcional, pero es código duplicado innecesario.

---

## 🟢 Errores Menores / Code Smells

### 16. Typo en `HistorialPaloma::getActivitylogOptions()`
**Archivo:** `app/Models/HistorialPaloma.php` — línea ~12

```php
->useLogName('Hisroial Paloma');  // ❌ 'Hisroial'
```

**Debería ser:** `'Historial Paloma'`

---

### 17. `Guard::esMiembro()` — `escribiente` es `BelongsToMany` pero se usa `contains()` con string
**Archivo:** `app/Models/Guard.php` — línea ~96

```php
$this->escribiente->contains('id', $user->id);
```

El método `contains('id', $user->id)` funciona en colecciones de Eloquent, pero si `escribiente` no fue cargado, `$this->escribiente` devuelve una relación, no una colección.

---

### 18. `User::HasPermisos()` — método con mayúscula (PSR-1)
**Archivo:** `app/Models/User.php` — línea ~110

```php
public function HasPermisos(string $permiso): bool  // ❌ camelCase incorrecto
```

**Debería ser:** `hasPermisos()` (primer letra minúscula).

---

### 19. `Guard::esMiembro()` — `escribiente` es relación, no propiedad
**Archivo:** `app/Models/Guard.php`

```php
public function escribiente(): BelongsToMany
```

En `esMiembro()` se accede como `$this->escribiente` (propiedad), lo que trigger lazy-load. Si la relación no fue cargada, el primer acceso ejecuta una query.

---

### 20. `NovedadPersonalController::destroy()` — no verifica pertenencia a guardia
**Archivo:** `app/Http/Controllers/NovedadPersonalController.php` — línea ~30

```php
abort_unless($novedadPersonal->guard_id === $guardia->id, 404);
```

Esto está bien, pero el `AutorizaOperacionGuardia` ya verifica que la guardia esté abierta. La verificación adicional es redundante pero no dañina.

---

### 21. `NovedadesController::store()` — `time` como string en request vs datetime en model
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~52

El campo `time` se valida como `'date_format:H:i'` (string) pero el modelo lo castea como `'datetime:H:i'`. Al hacer `News::create(['time' => '08:30'])`, el cast puede fallar.

---

### 22. `Guard::esMiembro()` no verifica si `$user` es null antes de acceder
**Archivo:** `app/Models/Guard.php` — línea ~93

```php
public function esMiembro(?User $user): bool
{
    if (!$user) { return false; }  // ✅ esto está bien
    ...
}
```

Esto está correcto, pero en los controladores se llama sin verificar null en algunos lugares.

---

### 23. `SalidaVehiculoController::store()` — doble validación de kms
**Archivo:** `app/Http/Controllers/SalidaVehiculoController.php` — líneas ~64-71

Se valida `kms_sale` y `kms_entra` en la validación principal (como nullable), y luego se re-validan como required dentro del `if (!$vehiculo->sin_cuentakilometros)`. Esto es redundante y puede causar confusión.

---

### 24. `NovedadesController::store()` — `time` se guarda como string pero se cast a datetime
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~80

```php
'novedad = News::create([
    ...$data,  // 'time' es string '08:30'
]);
```

El modelo cast `'time' => 'datetime:H:i'`. Un string `'08:30'` no se parsea correctamente como datetime.

---

### 25. `NovedadPersonal` y `NovedadRancho` — no hay policies registradas
**Archivo:** `app/Providers/AppServiceProvider.php`

No se registran policies para estos modelos. Si se usan `@can` en las vistas, fallarán con error silencioso.

---

### 26. `NovedadRanchoController::update()` — `menu` field inconsistency
**Archivo:** `app/Http/Controllers/NovedadRanchoController.php`

El `NovedadRancho` model tiene `'menu'` en `$fillable`, pero el controller actualiza `RanchoMenu` con los campos `menu_desayuno`, etc. El campo `menu` de `NovedadRancho` nunca se usa.

---

### 27. `NovedadesController::update()` — eliminación de adjunto anterior puede fallar
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~130

```php
} elseif ($novedad->adjuntos()->where('news_id', $novedad->id)->first()) {
    $adjunto = $novedad->adjuntos()->where('news_id', $novedad->id)->first();
    Storage::disk('guardias')->delete($adjunto->file_path);
    $adjunto->delete();
}
```

Se hace `where('news_id', $novedad->id)` cuando la relación ya es `hasMany` sobre `news_id`. Debería ser `$novedad->adjuntos()->first()` o `$novedad->adjuntos->first()`.

---

### 28. `NovedadesController::update()` — thumbnail con `.png` hardcodeado
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~125

```php
$nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
```

Si el archivo original es `.jpg`, `basename($name, '.png')` no elimina nada y el nombre puede ser confuso.

---

### 29. `NovedadesController::store()` — mismo problema de thumbnail
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~95

Mismo problema: `.png` hardcodeado para thumbnails.

---

### 30. `Documentos.php` Livewire — `formArchivo` no se resetea en `openEdit()`
**Archivo:** `app/Livewire/Documentos.php` — línea ~157

```php
public function openEdit(int $documentoId)
{
    // ...
    $this->formArchivo = null;  // ✅ esto está bien
    // ...
}
```

Esto está correcto, pero si el usuario sube un nuevo archivo y luego cancela, el archivo temporal puede persistir.

---

### 31. `NovedadesController::update()` — `time` validation format vs model cast
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~108

```php
'time' => 'required|date_format:H:i',  // String
```

El modelo cast `'time' => 'datetime:H:i'`. Debería usarse `'time' => 'required|date_format:H:i'` y luego convertir a Carbon antes de guardar, o cambiar el cast del modelo.

---

### 32. `Guard::esMiembro()` — `escribiente` relación con pivot `hora_inicio`/`hora_fin` no usados
**Archivo:** `app/Models/Guard.php` — línea ~71

```php
public function escribiente(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'guardia_escribientes', 'guardia_id', 'escribiente_id')
        ->withPivot('hora_inicio', 'hora_fin');
}
```

Los campos pivot `hora_inicio` y `hora_fin` se definen pero nunca se usan en ninguna consulta ni vista.

---

### 33. `NovedadPersonalController::destroy()` — no verifica que la novedad pertenezca al usuario
**Archivo:** `app/Http/Controllers/NovedadPersonalController.php` — línea ~30

Cualquier usuario autorizado puede eliminar cualquier novedad personal de la guardia, no solo la suya.

---

### 34. `NovedadRanchoController::update()` — no verifica que los datos pertenezcan a la guardia
**Archivo:** `app/Http/Controllers/NovedadRanchoController.php` — línea ~20

El `updateOrCreate` usa `guard_id` del parámetro `$guardia`, lo cual está bien, pero no hay verificación de que las unidades enviadas pertenezcan a la unidad de la guardia.

---

### 35. `SalidaVehiculo::calcularKmsYlitros()` — `$consumo_usado` asigna `consumo_litros_por_km` en vez de litros usados
**Archivo:** `app/Models/SalidaVehiculo.php` — línea ~68

```php
$this->consumo_usado = $vehiculo->consumo_litros_por_km;  // ❌ debería ser litros reales
```

`consumo_usado` se asigna con el consumo por km del vehículo, no con los litros realmente usados. Debería ser `$this->consumo_usado = $this->litros`.

---

### 36. `NovedadesController::store()` — `time` validation no considera formato datetime
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~52

```php
'time' => 'required|date_format:H:i',  // Valida como string
```

El modelo tiene `'time' => 'datetime:H:i'`. Al guardar un string como `'08:30'`, Laravel intentará convertirlo a Carbon y puede fallar.

---

### 37. `SalidaVehiculoController::store()` — validación `kms_entra` `gt:kms_sale` puede fallar con null
**Archivo:** `app/Http/Controllers/SalidaVehiculoController.php` — línea ~67

```php
'kms_entra' => 'nullable|integer|min:0|gt:kms_sale',
```

Si `kms_sale` es null (porque no se re-validó), la regla `gt:kms_sale` compara con null y puede fallar.

---

### 38. `NovedadesController::store()` — `time` como string en `date_format`
**Archivo:** `app/Http/Controllers/NovedadesController.php` — línea ~52

La validación `date_format:H:i` devuelve un string. Cuando se pasa a `News::create()`, el cast `'time' => 'datetime:H:i'` en el modelo intentará parsear `'08:30'` como datetime, lo cual puede generar un objeto Carbon con fecha 1970-01-01.

---

### 39. `Guard::esMiembro()` — `escribiente` relación con pivot no se usa
**Archivo:** `app/Models/Guard.php`

Los campos pivot `hora_inicio` y `hora_fin` se definen en la relación pero nunca se consultan ni se muestran en las vistas.

---

### 40. `NovedadPersonalController::destroy()` — no verifica ownership
**Archivo:** `app/Http/Controllers/NovedadPersonalController.php` — línea ~30

Cualquier usuario con permiso puede eliminar cualquier novedad personal de la guardia abierta, no solo la propia.

---

## 📋 Resumen

| Categoría | Cantidad |
|-----------|----------|
| 🔴 Críticos (rompen funcionalidad) | 6 |
| 🟡 Moderados (pueden causar bugs) | 9 |
| 🟢 Menores / Code Smells | 25 |
| **Total** | **40** |

### Top 5 errores más importantes a corregir:

1. **`SoftDeletes` mal importado** en `User.php` → Error fatal
2. **Trait `softDeletes` en minúsculas** en `User.php` → Error fatal
3. **`path.public` sobrescrito** en `AppServiceProvider` → Assets no sirven
4. **`Guard::Hoy()` con parámetros incorrectos** en `GuardiaController` → Error fatal
5. **`Guard::esMiembro()` lazy-load** → N+1 queries / datos incorrectos
