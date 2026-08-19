# SISNOVEDADES — AGENTS.md

**Stack:** Laravel 13.17 + Livewire 4.0 + Alpine.js + Bootstrap 5.2.3 + Tailwind CSS 4.0.7 + AdminLTE 3.16
**Ubicación:** `c:/laragon/www/novedades`
**PHP 8.3+ · Pest PHP · Vite · DomPDF · PHPSpreadsheet · Spatie ActivityLog · Spatie Laravel Backup**

---

## CSS: Bootstrap y Tailwind coexisten

Ambos frameworks están activos. **No mezclar clases de ambos en un mismo componente nuevo.** Verificar qué CSS usa cada vista/componente existente antes de escribir HTML/Blade. AdminLTE 3.16 se basa sobre Bootstrap — no usar clases de AdminLTE con Tailwind ni viceversa.

---

## Iconografía (FontAwesome 6)

Todo elemento interactivo con ícono en AdminLTE —tabs, botones, links, acciones de tabla, badges, etc.— debe usar **FontAwesome 6** (`fa-solid fa-...`, o `fa-regular`/`fa-brands` cuando corresponda) con un ícono representativo. Es el estándar por defecto del proyecto.

- Ícono genérico por defecto: `fa-building-shield`
- Íconos representativos según el nombre del tab/unidad (ej: `fa-tower-broadcast` para comunicaciones, `fa-truck` para transporte, `fa-boxes-stacked` para logística, `fa-star` para comando)
- Ícono para "Todas" / vista general: `fa-layer-group`

---

## Metodología obligatoria antes de escribir código

1. **Definición y Desglose del Problema** — Entender el alcance exacto, qué archivos se tocan, qué dependencias existen.
2. **Selección de la Herramienta** — `codebase-memory` para código propio del proyecto. `anythingllm` para documentación de librerías externas (Spatie, Livewire docs, etc.).
3. **Planificación por Fases** — Descomponer en pasos pequeños y verificables.
4. **Pruebas y Depuración** — Verificar que funciona antes de reportar como completado.

---

## Patrones de código adoptados

### CRUD Livewire — Niveles

- **Nivel 1 (Simple):** `#[Computed]` + `WithPagination` + `UsesBootstrapPagination` + `mount()` con `$this->authorize('viewAny')` + `updatedSearch()` con `$this->resetPage()` + formulario inline con `wire:confirm` para eliminar.
- **Nivel 2 (Intermedia):** `#[Computed]` + sin paginación (`->get()`) + `mount()` con `$this->authorize('viewAny')` + `updatedSearch()` + modal de formulario con `x-ops-card` + `confirmDelete()` / `executeDelete()` + checkboxes agrupados por módulo.
- **Nivel 3 (Alta):** Formularios complejos + relaciones múltiples + validaciones custom + múltiples catálogos dependientes.
- **Nivel 4 (Muy alta):** Lógica de negocio crítica (búsquedas multi-campo, `leftJoin` jerárquico, soft delete, force delete, transacciones).

## Reglas de autonomía para agentes de IA (Qwen/otros)

### 🚫 Never do (parada dura, sin excepción)
- Nunca hacer `forceDelete()` o eliminación permanente de un modelo sin antes
  verificar y limpiar explícitamente todas las FK con `RESTRICT` que apunten a él
  (ver lista completa en historial de FKs hacia `users.id` en memoria del proyecto).
  Bug real: `ejecutarEliminacionPermanente()` rompió por esto en Users.
- Nunca modificar una migración ya corrida en producción/Supabase. Crear una nueva.
- Nunca escribir SQL crudo en una migración sin bifurcar por driver
  (`DB::getDriverName() === 'pgsql'`) si el proyecto sigue siendo dual MySQL/Postgres.
- Nunca borrar o modificar un test existente para que "pase" sin aprobación explícita.
- Nunca commitear credenciales, `.env`, ni tocar `APP_URL`/config de conexión a BD.
- Nunca eliminar código de un controlador "legacy" (ej. GuardiaController,
  VehiculoController) sin confirmar primero con grep/codebase que 0 rutas o vistas
  lo siguen referenciando.
- Nunca reemplazar el patrón de confirmaciones del proyecto (SweetAlert2 vía
  `confirmarAccion()`) por modales custom nuevos sin que se pida explícitamente.

### ⚠️ Ask first (pausar y confirmar antes de ejecutar)
- Antes de modificar `phpunit.xml`, `tests/Pest.php` o `.env.testing`.
- Antes de tocar cualquier migración de base de datos o cambiar un schema.
- Antes de agregar una dependencia nueva a `composer.json` o `package.json`.
- Antes de modificar `config/backup.php`, `config/database.php` o cualquier
  archivo de configuración de conexión.
- Antes de tocar policies o el sistema de permisos (Spatie) — puede romper
  reglas de negocio ya validadas (ej. bloqueo de eliminar rol 'admin').
- Antes de refactorizar lógica de un componente Livewire ya migrado y probado
  en producción (ej. Guardias, Users) — proponer el cambio primero.
- Antes de eliminar un archivo `.blade.php` o controlador que parezca sin uso,
  si no se corrió antes una verificación de referencias vía codebase.

### ✅ Loop obligatorio al terminar cualquier tarea de código
1. Correr `php artisan test` (o el subset de Pest relevante) y reportar resultado
   completo (pass/fail), no solo "listo".
2. Si algún test falla, intentar corregir y volver a correr antes de dar la tarea
   por terminada — no dejar tests rotos sin avisar explícitamente.
3. Actualizar `AGENTS.md` con cualquier decisión, bug nuevo o patrón descubierto.
4. Re-indexar con `codebase` los archivos tocados.
5. Resumen final: qué se tocó, qué se testeó, qué quedó pendiente/TODO.

---

## Entorno de testing — SQLite in-memory

Desde 2026-08-18, la suite de tests corre sobre SQLite in-memory (`:memory:`), completamente aislada de la BD de desarrollo (`novedades`, MySQL).

### Configuración

| Archivo | Estado |
|---------|--------|
| `phpunit.xml` | `<php>` con `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` |
| `config/database.php` | Conexión `sqlite` con `foreign_key_constraints => true` |
| `.env.testing` | Variables testing (APP_ENV, DB, CACHE, SESSION, QUEUE, MAIL) |
| `tests/Pest.php` | `RefreshDatabase::class` solo en `Feature` |
| `tests/Feature/SanityCheckTest.php` | Valida explícitamente `sqlite` + `:memory:` |

### Limitaciones SQLite

- SQLite **no soporta `DROP COLUMN`** cuando hay índices `UNIQUE` sobre la columna.
  Migraciones que intentan borrar columnas con `unique()` en SQLite deben usar
  Schema Builder estándar (no SQL crudo) o `return;` para saltar.
- Migraciones con `DB::statement()` bifurcadas por driver (`sqlite`/`pgsql`/`mysql`)
  son compatibles. Las que tienen `return;` para SQLite dejan columnas existentes —
  los modelos auto-generan valores para evitar errores NOT NULL (ej: `Item::boot()`).

### Verificación de integridad

Siempre verificar que la BD MySQL no fue tocada:
```bash
php artisan tinker --execute="echo \App\Models\User::count();"  # antes
php artisan test --no-coverage
php artisan tinker --execute="echo \App\Models\User::count();"  # después
```
Ambos valores deben ser idénticos.

### Resultados actuales

285 tests · 263 passed · 3 failed · 17 errors · 2 skipped

**Errores pre-existentes (no causados por aislamiento):**
- 8x `no such table: users` — Unit tests sin `RefreshDatabase`
- 5x `Route [dashboard] not defined` — Rutas inexistentes
- 2x `last_name` Livewire — Propiedad removida
- 1x `tipo_combustible` NOT NULL — Factory incompleta
- 1x `UNIQUE constraint` — Factory duplica nombre

**Fallos pre-existentes:**
- 2x `SecurityTest` — Assertions de contenido HTML (Passkeys, "Update password")
- 1x `CategoriaTest` — Espera 2 hijas recursivas, obtiene 1

### Pendiente
- Migraciones con `return;` para SQLite: verificar que las columnas dejadas
  no rompan tests. Si es necesario, crear migraciones nuevas solo-SQLite
  que dropeen las columnas (evitar DROP COLUMN con UNIQUE).
- Unit tests sin `RefreshDatabase`: agregar trait o migrar a Feature tests.


### Componente Livewire — Estructura base

```php
class NombreComponent extends Component
{
    public $search = '';
    public $showForm = false;
    public $formTipo = 'create';
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    #[Computed]
    public function registros() { /* query con filtros */ }

    public function crear() { $this->authorize('create', Modelo::class); $this->showForm = true; }

    public function guardar()
    {
        $this->authorize('create', Modelo::class);
        $this->validate([...]);
        $this->loading = true;
        try { Modelo::create($this->only([...])); $this->successMsg = '...'; $this->reset([...]); }
        catch (\Exception $e) { $this->errorMsg = 'Error: ' . $e->getMessage(); }
        finally { $this->loading = false; }
    }

    public function render() { return view('livewire.nombre', ['registros' => $this->registros()]); }
}
```

### Convenciones clave
- Estado UI en propiedades públicas (`showForm`, `showTrash`, `showPreview`)
- `#[Computed]` para consultas con filtros reactivos
- `wire:loading` para feedback visual · `wire:confirm` para eliminar (sin modales JS externos)
- `dispatch()` para eventos entre componentes · `WithFileUploads` para archivos
- Validación reactiva con `updated($propertyName)` y `validateOnly()`
- Mensajes en `$successMsg` / `$errorMsg` (no session flash)
- Traits: `WithPagination`, `UsesBootstrapPagination`, `WithFileUploads`

### Toast de notificaciones (Fase 2 — migración de alertas Blade)
Las alertas auto-cerrables (`x-data + x-init + setTimeout + $wire.set`) se eliminaron de las vistas Blade. Ahora se usan watchers Livewire 4 + `window.mostrarToast()` (SweetAlert2 toast arriba a la derecha):

```blade
{{-- En el blade: NO hay bloques @if alert --}}

@script
<script>
    $wire.$watch('successMsg', (valor) => {
        mostrarToast('success', valor);
    });

    $wire.$watch('errorMsg', (valor) => {
        mostrarToast('error', valor);
    });
</script>
@endjs
```

El helper `window.mostrarToast(tipo, mensaje)` vive en `public/js/confirmaciones.js`. No repetir.

**14 componentes migrados a este patrón:** categorias-documentos, documentos, admin/users, conductores, guardias, palomar/estados, notificaciones (successMsg solo), permisos, organismos, oficinas, palomares, roles, vehiculos, vehiculos/tipos.

**Pendientes de revisión manual:** vuelos/resultados-form.blade.php (sin auto-close), enviar-guardia-email/enviar-guardia-email.blade.php (propiedad `mensajeExito`).

### Patrón ops-panel
Modales con `x-ops-card` + `x-teleport="body"` + clase `is-open` (no `x-show`). Usado en BackupManager, GuardiaAcciones, y otros modales de acción.

### Traits y Gates
- `HasPermisos('nombre_permiso')` — trait en User para chequeo de permisos
- `Gate::before()` global: `isSuperAdmin() ? true : null` — SuperAdmin exento de TODO
- `isAdmin()` solo en GuardiaPolicy y UserPolicy (decisión explícita)

---

## Estado de la migración Blade → Livewire

**69 componentes Livewire implementados · 31 migraciones completadas (27 de CRUD + 3 parciales + 1 landing)**

| Estado | Nivel | Componentes |
|--------|-------|-------------|
| ✅ Completado | 1 (Simple) | Oficinas, Organismos, TipoVehiculo, EstadoPaloma |
| ✅ Completado | 2 (Intermedia) | Permisos, Roles, Notificaciones, Logs |
| ✅ Completado | 3 (Alta) | Vehículos, Palomares, Palomas, Conductores, Vuelos |
| ✅ Parcial | 4 (Muy alta) | Guardias (reducido a show/Hoy/pdf) — Pendiente: Users |
| ✅ Landing | — | 14 componentes (Hero, Navbar, Footer, Crucigrama, Tetris, Sudoku, SopaLetras, etc.) |

---

## Modelos clave, Policies y bloqueo de eliminación

**31 Policies** registradas en `AppServiceProvider` via `Gate::policy()`. **~173 permisos** en 30 módulos, 6 roles.

### Bloqueo de eliminación
- **Rol `admin`:** no se puede eliminar (`RolPolicy::delete`)
- **SuperAdmin:** no se puede eliminar/editar (`UserPolicy::update/delete` protegen)
- **Oficina:** `delete` solo admin, no delegable
- **Documentos:** usa `Response` en vez de `bool` en Policy
- **GuardiaPdfDestinatario:** tiene `before()` que exime a SuperAdmin y miembros de guardia del día

### Roles
| Rol | Alcance |
|-----|---------|
| `admin` | Total — todos los permisos |
| `capitan_de_servicio` | Crear/cerrar guardia, eliminar novedad |
| `oficial_de_dia` | Crear guardia, editar cualquier novedad |
| `escribiente` | Registrar novedades en su guardia |
| `colombofilo` | CRUD palomares |
| `visitante` | Solo vistas públicas |

---

## Cambios recientes

### 2026-08-19 — Correos fallidos: persistencia de modo de envío (con_adjuntos / con_zip)

**Problema:** El botón "Reintentar" de `guardia_correos_fallidos` siempre reenviaba el PDF simple,
sin importar si el envío original fue con adjuntos embebidos o como ZIP, porque esa información
nunca se guardó en la tabla de fallos.

**Solución:** Agregar columnas `con_adjuntos` (boolean, default false) y `con_zip` (boolean,
default false) a ambas tablas (`guardia_correos_fallidos` y `guardia_correos_enviados`),
y propagarlas en los tres puntos de inserción + el reintento.

**Migración:** `database/migrations/2026_08_18_150000_add_envio_mode_to_guardia_correos_tables.php`
- Agrega `con_adjuntos` + `con_zip` a `guardia_correos_fallidos` (después de `motivo`)
- Agrega `con_adjuntos` + `con_zip` a `guardia_correos_enviados` (después de `message_id`)
- Backfill: valores `false` para registros existentes

**Archivos modificados (4):**
- `app/Jobs/EnviarNovedadGuardiaMail.php` — `registrarFallo()` ahora guarda `$this->incluirAdjuntos` / `$this->enviarZip`; `handle()` guarda los mismos campos en `guardia_correos_enviados`
- `app/Console/Commands/ProcesarRebotesCommand.php` — Al insertar en `guardia_correos_fallidos` desde rebote IMAP, copia `con_adjuntos`/`con_zip` desde `$envioOriginal`
- `resources/views/livewire/correos-fallidos/correos-fallidos.php` — `reintentar()` lee `$fallo->con_adjuntos` / `$fallo->con_zip`, regenera PDF/ZIP en el mismo modo y pasa los 7 parámetros a `dispatchSync()`

**Patrón para reintento fiel:**
```php
// Leer modo de envío original
$incluirAdjuntos = (bool) $fallo->con_adjuntos;
$enviarZip = (bool) $fallo->con_zip;

// Regenerar PDF/ZIP en el mismo modo
$pdfContent = $incluirAdjuntos
    ? GuardiaPdfGenerator::generarConAdjuntos($guardia)
    : GuardiaPdfGenerator::generar($guardia)->output();

$zipContent = $enviarZip
    ? GuardiaZipGenerator::generar($guardia, $pdfContent)
    : null;

// Reenviar con los mismos parámetros
EnviarNovedadGuardiaMail::dispatchSync(
    $guardia, $usuario, $nombreRemitente,
    $incluirAdjuntos, $pdfContent,
    $enviarZip, $zipContent,
);
```

**Investigación previa:**
- `guardia_correos_fallidos`: sin columnas de modo de envío (solo id, guardia_id, user_id, email, motivo, resuelto_at, timestamps)
- `guardia_correos_enviados`: sin columnas de modo de envío (solo id, guardia_id, user_id, email, message_id, rebotado_en, timestamps)
- `BadgeCorreosFallidosCount` (`badge-correos-fallidos.php`): escucha `#[On('correos-fallidos-actualizado')]` → invalida `#[Computed] pendientes()` → refresca badge en show.blade.php
- `BadgeCorreosFallidosCount` NO fue tocado (no existe como archivo independiente; el componente está en `badge-correos-fallidos.php`)
- `enviar-guardia-email.php` referencia: genera PDF con `GuardiaPdfGenerator::generar($guardia)->output()` (simple) o `::generarConAdjuntos($guardia)` (adjuntos embebidos), y ZIP con `GuardiaZipGenerator::generar($guardia, $pdfContent)`

### 2026-08-18 — Envío de novedades por correo: afterResponse() para evitar timeout HTTP 503

**Problema:** El método `enviar()` del componente `enviar-guardia-email` hacía un `foreach` sobre `$usuarios` llamando `EnviarNovedadGuardiaMail::dispatchSync()` para cada uno, de forma síncrona dentro del mismo request HTTP. Con ~30 destinatarios y adjuntos pesados (~8 MB por correo), el front-end (IIS) cortaba con timeout 503 antes de terminar.

**Solución:** Reemplazar `dispatchSync()` en un `foreach` bloqueante por `dispatch()->afterResponse()`. La respuesta HTTP se envía al navegador ANTES de procesar los N envíos de correo. El PDF y ZIP se generan UNA sola vez antes del `dispatch`, igual que antes.

**¿Por qué `afterResponse()` y no `Queue::push()`?**
El proyecto ya decidió (ver `EnviarNovedadGuardiaMail.php` docblock) que `ShouldQueue` con `queue:work` es frágil en este entorno. `afterResponse()` ejecuta el closure en el mismo request pero después de que la respuesta HTTP fue entregada — no requiere worker, no requiere tabla "jobs", y no depende de la cola. Para el volumen actual (~30 destinatarios) es suficiente.

**Cambios:**
- `enviar-guardia-email.php`: `enviar()` ahora dispara `novedades-enviadas` ANTES del closure, limpia el estado, muestra "Enviando novedades por correo...", y dispara `dispatch(fn() { foreach... })->afterResponse()`. Propiedad `$fallidosCount` eliminada.
- `enviar-guardia-email.blade.php`: Alerta siempre `alert-info`, botón "Ver correos fallidos" siempre visible. Listener `novedades-enviadas` siempre cierra el panel (sin chequeo de `fallidos`).
- `CorreosFallidos` (no tocado): Ya escucha `#[On('novedades-enviadas')]` para invalidar su caché `Computed`. Es la fuente de verdad del resultado final.

**Patrón a usar para otros envíos masivos:**
```php
// 1. Disparar evento de UI antes del closure
$this->dispatch('envio-iniciado');

// 2. Limpiar estado y mostrar mensaje de "en curso"
$this->mensajeExito = 'Enviando...';

// 3. Dispatch del closure después de responder
dispatch(function () use ($datos) {
    foreach ($datos as $item) {
        MiJob::dispatchSync($item);
    }
})->afterResponse();
```

### 2026-08-17 — Inventario: eliminación de abreviatura de categoría y código de ítem
Se eliminó por completo el concepto de "abreviatura de categoría" (`categorias.codigo_abreviatura`) y el campo "código" de ítems (`items.codigo`). La abreviatura autogeneraba códigos automáticos de ítems (ej: `EQC-0001`), funcionalidad que se eliminó por completo.

**Archivo:** `database/migrations/2026_08_17_000001_remove_codigo_abreviatura_categorias_and_codigo_items.php`

**Archivos modificados (12):**
- `app/Models/Categoria.php` — Quitado `codigo_abreviatura` de `$fillable`, quitados hooks `creating`/`updating` de autogeneración, eliminado método `generarAbreviaturaUnica()`
- `app/Models/Item.php` — Quitado `codigo` de `$fillable`, eliminado accessor `codigo()` de `Attribute`
- `app/Livewire/Inventario/CategoriasCatalogo.php` — Quitadas propiedades `codigo_abreviatura` / `editCodigoAbreviatura`, validaciones y uso en `agregar()`/`saveEdit()`; búsqueda solo por `nombre`
- `app/Livewire/Inventario/ItemsCatalogo.php` — Quitadas propiedades `codigo` / `codigoAuto`, eliminados métodos `updatedCategoriaId()`, `updatedCodigo()`, `generarCodigoSugerido()`; búsqueda solo por `nombre`
- `app/Imports/ItemsImport.php` — Quitada validación y uso de columna `codigo`
- `app/Exports/ItemsPlantillaExport.php` — Quitada columna `codigo` de plantilla descargable
- `app/Exports/ItemUnidadesPlantillaExport.php` — Quitada columna `codigo_item` de plantilla
- `resources/views/livewire/inventario/categorias-catalogo.blade.php` — Quitados inputs y columna "Abreviatura"
- `resources/views/livewire/inventario/items-catalogo.blade.php` — Quitada columna "Código", form-group de código, aviso de abreviatura; placeholder actualizado
- `resources/views/livewire/inventario/lotes-stock.blade.php` — `$item->codigo` → `$item->nombre` en todas las vistas
- `resources/views/livewire/inventario/vencidos-en-terceros.blade.php` — Idem
- `resources/views/livewire/inventario/movimientos-inventario.blade.php` — Idem
- `resources/views/livewire/inventario/unidades-individuales.blade.php` — Idem
- `resources/views/livewire/inventario/entregas-inventario.blade.php` — Idem
- `resources/views/livewire/inventario/pdf/comprobante-entrega.blade.php` — Idem
- `app/Livewire/Inventario/LotesStock.php` — Busqueda solo por `nombre` (quitado `orWhere('codigo')`)
- `app/Livewire/Inventario/VencidosEnTerceros.php` — Idem

### 2026-08-17 — Vehículos: eliminación de controladores huérfanos
Se eliminaron `VehiculoController` y `MantenimientoVehiculoController` (ambos huérfanos) junto con sus vistas rotas y rutas en `web.php`. El CRUD de vehículos opera 100% vía Livewire (`app/Livewire/Vehiculos.php` + `MantenimientoModal`).

**Archivos eliminados:**
- `app/Http/Controllers/VehiculoController.php` — 280+ líneas, 0 rutas en web.php
- `app/Http/Controllers/MantenimientoVehiculoController.php` — 2 rutas en web.php, CRUD migrado a Livewire
- `resources/views/admin/vehiculos/create.blade.php` — rota (ruta inexistente)
- `resources/views/admin/vehiculos/edit.blade.php` — rota (ruta inexistente)
- `resources/views/admin/vehiculos/index.blade.php` — rota (ruta inexistente)
- `resources/views/admin/vehiculos/show.blade.php` — rota (ruta inexistente)
- `resources/views/admin/vehiculos/mantenimientos/index.blade.php` — rota (ruta inexistente)
- `routes/web.php` — quitado import de ambos controllers + 2 rutas de mantenimientos

---

## Qué NO migrar / no tocar

- `web/index.blade.php` — Landing estática
- `auth/*` — Login (página única, no necesita reactividad)
- `admin.guardias.pdf.*` — Generación de PDF (renderizado servidor-side)
- `layouts/*`, `partials/*`, `emails/*` — Templates compartidos
- `admin.guardias.pdf-preview` — Ruta pública de preview HTML
- Controladores ya migrados y eliminados: `OficinaController`, `OrganismoController`, `TipoVehiculoController`, `EstadoPalomaController`, `PermisoController`, `RolController`, `ActivityLogController`, `ConductorController`, `PalomarController`, `PalomaController`, `VueloController`, `VehiculoController`, `MantenimientoVehiculoController` — todos eliminados y sin referencias residuales.

---

## Reglas de autorización relevantes

- `Gate::before()` global: `isSuperAdmin() ? true : null` — exime a SuperAdmin de TODOS los gates
- `isAdmin()` solo en GuardiaPolicy (7 métodos) y UserPolicy (update/delete) por decisión explícita
- Resto de Policies: 100% basadas en `HasPermisos()`, sin atajos de rol
- Roles operativos (capitán/oficial/escribiente) acceden por lógica de rol EN la Policy, no por gate
- `seeded_permissions_locked` en tabla `rols`: si es `true`, el RolSeeder no reasigna permisos automáticamente
- **Riesgo:** correr `RolSeeder` después de asignar permisos manualmente **borra las asignaciones manuales** (`sync()` reemplaza todo)
- Nuevo permiso: agregar en `PermisoSeeder` → correr `php artisan db:seed --class=PermisoSeeder` → asignar en panel de Roles → agregar chequeo en Policy o `Gate::define()` en AppServiceProvider

---

## ⚠️ Bug conocido: Livewire 4 — `$wire.$off()` no existe (MethodNotFoundException 500)

### Síntoma
Error 500 en `POST /livewire-{id}/update`:
```
Unable to call component method. Public method [$off] not found on component
```
El proxy `$wire` de Livewire 4 interpreta `$wire.$off(...)` como llamada a un método PHP público llamado `off` en el componente → `MethodNotFoundException`.

### Causa raíz
Livewire 4 **no tiene** `$wire.$off()` como método. En Livewire 4, `$wire` es un proxy de Alpine.js que intercepta llamadas a métodos del componente. `$off` no existe como método interno (a diferencia de `$refresh`, `$set`, `$get`).

### Patrón INCORRECTO (NO usar — causa error 500)
```blade
@script
<script>
    // ❌ ERROR: $wire.$off() no existe en Livewire 4
    $wire.$off('abrir-modal');
    $wire.on('abrir-modal', () => { ... });
</script>
@endscript
```

### Patrón correcto (usar siempre)
```blade
@script
<script>
    // ✅ Livewire 4 maneja deduplicación de listeners internamente
    $wire.on('abrir-modal', () => {
        document.getElementById('miModal').classList.add('is-open');
    });

    $wire.on('cerrar-modal', () => {
        cerrarOpsPanel('miModal');
    });
</script>
@endscript
```

### Componentes corregidos (2026-08-18)
Todos los componentes con `$wire.$off()` fueron corregidos eliminando las llamadas obsoletas:
| Componente | Líneas corregidas |
|------------|-------------------|
| `novedades-guardia.blade.php` | 567-568 |
| `salidas-vehiculo.blade.php` | 408-411 |
| `pase-panel.blade.php` | 169-170 |
| `historial-grados-panel.blade.php` | 160-161 |
| `historial-estado-panel.blade.php` | 173-174 |
| `comision-panel.blade.php` | 227-230 |
| `enviar-guardia-email.blade.php` | 283-284 |
| `editar-novedad-modal.blade.php` | 186 |
| `items-catalogo.blade.php` | 305-308 |
| `unidades-individuales.blade.php` | 396-399 |

### Componentes NO afectados
- `backup-manager.blade.php` — Usa `$wire.showRestoreModal` con Alpine `$watch` (patrón reactivo, no JS vainilla)
- Componentes que usan `showForm` + `:class="{'is-open': $wire.showForm}"` — patrón reactivo de Livewire/Alpine

### Regla para futuros componentes
**NO usar** `$wire.$off(...)` en Livewire 4 — no existe como método. Simplemente usar `$wire.on('evento', callback)` directamente. Livewire 4 maneja la deduplicación de listeners internamente. Si el componente usa `showForm` reactivo, no hay problema de listeners acumulados.

---

## Cambios recientes

### 2026-08-19 — Correos fallidos: polling temporal para corregir race condition post-afterResponse

**Problema:** El evento `novedades-enviadas` se dispara ANTES del closure `afterResponse()`
(linea 304 de este documento). `correos-fallidos.php` escucha ese evento con `#[On]` e
invalida `#[Computed] fallos`, pero al momento de invalidar aún no existen fallos nuevos
(en la BD los inserta el afterResponse que corre DESPUÉS de la respuesta HTTP).

**Causa raíz:** `dispatch(...)->afterResponse()` corre después de que la respuesta HTTP
ya fue enviada al navegador. Un `dispatch()` de evento Livewire normal dentro del closure
NO puede llegar al front-end porque los eventos viajan en el payload de la respuesta AJAX,
que para ese momento ya se cerró.

**Solución:** Polling temporal activado por Alpine.js `$watch` + `wire:poll` condicional
con `x-if`. Ventana de 60 segundos tras `novedades-enviadas`.

** Patrón de implementación:**

```php
// En el componente que escucha (correos-fallidos.php):
public bool $pollActivo = false;
public ?int $pollHasta = null;

#[On('novedades-enviadas')]
public function refrecar(): void
{
    $this->pollActivo = true;
    $this->pollHasta = (int) floor(now()->addSeconds(60)->timestamp);
    unset($this->fallos); // invalidar caché para primer poll
}

public function stopPoll(): void
{
    $this->pollActivo = false;
    $this->pollHasta = null;
}
```

```blade
{{-- En el blade del componente que escucha --}}
<template x-if="$wire.pollActivo">
    <div wire:poll.5s="refrecar"></div>
</template>

@script
<script>
    $wire.$watch('pollActivo', (activo) => {
        if (activo) {
            setTimeout(() => {
                $wire.stopPoll();
            }, 60000);
        }
    });
</script>
@endscript
```

**Flujo:**
1. `enviar()` dispara `novedades-enviadas` → `refrecar()` activa `$pollActivo = true`
2. Alpine `$watch` detecta cambio → `x-if` renderiza `<div wire:poll.5s="refrecar">`
3. Cada 5s se hace `$refresh` → `#[Computed] fallos` se re-evalúa con datos frescos de BD
4. afterResponse() inserta fallos → primer poll posterior los lee
5. 60s después → `stopPoll()` → `$pollActivo = false` → `x-if` destruye el poll

**Archivos modificados (2):**
- `resources/views/livewire/correos-fallidos/correos-fallidos.php` — propiedades `$pollActivo`, `$pollHasta`, método `stopPoll()`
- `resources/views/livewire/correos-fallidos/correos-fallidos.blade.php` — `x-if` + `wire:poll` condicional + `$watch` con timeout

**No se tocó:** `enviar-guardia-email.php` (el dispatch de `novedades-enviadas` ya estaba en el lugar correcto),
`BadgeCorreosFallidosCount.php` (escucha `correos-fallidos-actualizado`, no `novedades-enviadas`),
ni ningún archivo de mail/PDF/ZIP.

**Investigación previa:**
- No hay Laravel Echo/broadcasting (Pusher/Reverb/Soketi) en el proyecto — descartado
- Patrón `wire:poll` condicional con `@if` ya existe (`estado-novedad.blade.php:1`) pero es estático
- Se eligió `x-if` + `$watch` porque `wire:poll` se evalúa al render inicial y no reacciona a cambios de estado

### 2026-08-18 — Novedades Guardia: fix validación `destinos` con array no-nullable

**Bug:** `nullable|required_if:direction,Expedido|array|min:1` en propiedad `public array $destinos = []`.
`nullable` solo exime cuando el valor es literalmente `null`. Como la propiedad tiene default `[]`,
nunca llega `null` al validador → `array|min:1` se evalúa incondicionalmente → falla con
*"al menos 1 elementos"* incluso para `direction='Recibido'` donde el campo es invisible.

**Fix:** reemplazo por closure en `resources/views/components/novedades-guardia/novedades-guardia.php:151-158`:
```php
'destinos' => [
    'array',
    function ($attribute, $value, $fail) {
        if ($this->direction === 'Expedido' && count($value) < 1) {
            $fail('Debés seleccionar al menos un destino.');
        }
    },
],
```

**Tests:** 3 nuevos tests en `tests/Feature/Livewire/NovedadesGuardiaDestinosTest.php` —
`Recibido` + `destinos=[]` pasa, `Expedido` + `[]` falla, `Expedido` + `['Bat2','Bat5']` pasa.

**Patrón a evitar:** `nullable|required_if:...|array|min:N` sobre propiedad `array $prop = []`.
`nullable` no funciona sobre arrays no-nullable. Usar closure para lógica condicional en arrays.

### 2026-08-18 — News: fixes bugs destino JSON + scopeUrgentes

**Migración** `2026_08_18_141214_change_destino_to_json_in_news.php` corregida:
- **Bug 1:** `esJson()` reemplazado por `decodificarComoArray()` — escalares JSON-válidos (`"105"`, `"true"`, `"null"`) ahora se envuelven en array, no se guardan como int/bool.
- **Bug 2:** `decodificarComoArray()` incluye split por coma (`"Batallon 1, Batallon 2"` → `["Batallon 1", "Batallon 2"]`). El inventario de datos encontró 0 valores con comas en producción, pero se incluyó como defensa para datos legacy futuros.
- `down()` simplificado: ya no referencia `esJson()` (que fue eliminado).

**Modelo** `News.php`:
- **Bug 3:** `scopeUrgentes()` corregido de `where('clasification', ['Urgente', 'Destello'])` a `whereIn('clasification', self::CLASIFICACIONES_URGENTES)`. El `where` con array no filtra por múltiples valores en Eloquent.
- **Impacto:** `scopeUrgentes()` no tenía llamadores en el código (0 callers), pero queda corregido para uso futuro en dashboards/notificaciones.

**Tests:** 12 nuevos tests (6 Pest + 6 PHPUnit migración), todos passing.

### 2026-08-17 — Inventario: eliminación de abreviatura de categoría y código de ítem
