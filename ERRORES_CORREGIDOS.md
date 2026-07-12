# Errores que daban fallo (ya corregidos)

---

## ✅ 1. `Guard::Hoy()` con parámetros incorrectos
**Archivo:** `app/Http/Controllers/GuardiaController.php` — línea 120

**Antes:**
```php
$guardia = Guard::Hoy('date', now()->toDateString())->first();
```

**Error:** El scope `scopeHoy($query)` no acepta parámetros. Al pasar `'date'` como primer arg, `$query` recibe un string y `whereDate()` falla.

**Ahora:**
```php
$guardia = Guard::Hoy()->first();
```

---

## ✅ 2. Relación `novedadesVehiculos` inexistente en Conductor
**Archivo:** `app/Http/Controllers/ConductorController.php` — líneas 71, 118

**Antes:**
```php
$conductor->load(['novedadesVehiculos' => ...]);
$conductor->novedadesVehiculos()->count()
```

**Error:** `Call to undefined method App\Models\Conductor::novedadesVehiculos()`

**Ahora:**
- Se cambió a `salidasVehiculos` (relación real)
- Se agregó la relación `salidasVehiculos()` al modelo `Conductor`

---

## ✅ 3. `Guard::esMiembro()` lazy-load de `escribiente`
**Archivo:** `app/Models/Guard.php` — línea 96

**Antes:**
```php
$this->escribiente->contains('id', $user->id);
```

**Error:** Si la relación no fue cargada eager, accede a una relación sin cargar → N+1 o resultado incorrecto.

**Ahora:**
```php
$this->escribiente()->where('users.id', $user->id)->exists();
```

---

## ✅ 4. `calcularKmsYlitros()` accede a `null` cuando vehículo fue borrado
**Archivo:** `app/Models/SalidaVehiculo.php` — línea 75

**Antes:**
```php
$vehiculo = $this->vehiculo;
if (!$vehiculo->sin_cuentakilometros && ...) // ❌ crash si $vehiculo es null
```

**Error:** `Call to a member function sin_cuentakilometros on null`

**Ahora:**
```php
if (!$vehiculo || $this->kms_sale === null || $this->kms_entra === null) {
    $this->kms_recorridos = null;
    $this->litros = null;
    $this->consumo_usado = null;
    return;
}
```

---

## ✅ 5. `path.public` sobrescrito apuntando a lugar incorrecto
**Archivo:** `app/Providers/AppServiceProvider.php` — línea 39

**Antes:**
```php
$this->app->bind('path.public', function () {
    return storage_path() . '/public_html';
});
```

**Error:** `public_path()` apuntaba a `storage/app/public_html` en vez de `public/`. Rompía assets, `storage:link`, etc.

**Ahora:**
```php
// path.public ya viene configurado por Laravel en public/
// No sobreescribir para no romper assets y storage:link
```

---

## ✅ 6. `SoftDeletes` mal importado en User.php
**Archivo:** `app/Models/User.php` — línea 22

**Antes:**
```php
use Illuminate\database\Eloquent\SoftDeletes;  // ❌ 'd' minúscula
```

**Error:** `Class "Illuminate\database\Eloquent\SoftDeletes" not found` → FATAL ERROR

**Ahora:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;  // ✅ 'D' mayúscula
```

---

## ✅ 7. Trait `softDeletes` en minúscula en User.php
**Archivo:** `app/Models/User.php` — línea 44

**Antes:**
```php
use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, softDeletes, LogsActivity;
```

**Error:** `Trait "softDeletes" not found` → FATAL ERROR

**Ahora:**
```php
use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, SoftDeletes, LogsActivity;
```

---

## 📋 Resumen

| # | Error | Estado |
|---|-------|--------|
| 1 | `Guard::Hoy()` con parámetros incorrectos | ✅ Corregido |
| 2 | Relación `novedadesVehiculos` inexistente | ✅ Corregido |
| 3 | `esMiembro()` lazy-load de `escribiente` | ✅ Corregido |
| 4 | `calcularKmsYlitros()` null access | ✅ Corregido |
| 5 | `path.public` sobrescrito incorrectamente | ✅ Corregido |
| 6 | `SoftDeletes` import mal escrito | ✅ Corregido |
| 7 | Trait `softDeletes` en minúscula | ✅ Corregido |
