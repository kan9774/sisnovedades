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

314 tests · 293 passed · 3 failed · 16 errors · 2 skipped

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

**15 componentes migrados a este patrón:** categorias-documentos, documentos, admin/users, conductores, guardias, palomar/estados, notificaciones (successMsg solo), permisos, organismos, oficinas, palomares, roles, vehiculos, vehiculos/tipos, apoyos.

**Pendientes de revisión manual:** vuelos/resultados-form.blade.php (sin auto-close), enviar-guardia-email/enviar-guardia-email.blade.php (propiedad `mensajeExito`).

### Patrón ops-panel
Modales con `x-ops-card` + `x-teleport="body"` + clase `is-open` (no `x-show`). Usado en BackupManager, GuardiaAcciones, y otros modales de acción.

### Traits y Gates
- `HasPermisos('nombre_permiso')` — trait en User para chequeo de permisos
- `Gate::before()` global: `isSuperAdmin() ? true : null` — SuperAdmin exento de TODO
- `isAdmin()` solo en GuardiaPolicy y UserPolicy (decisión explícita)

---

## Estado de la migración Blade → Livewire

**70 componentes Livewire implementados · 32 migraciones completadas (28 de CRUD + 3 parciales + 1 landing)**

| Estado | Nivel | Componentes |
|--------|-------|-------------|
| ✅ Completado | 1 (Simple) | Oficinas, Organismos, TipoVehiculo, EstadoPaloma |
| ✅ Completado | 2 (Intermedia) | Permisos, Roles, Notificaciones, Logs, TiposApoyo |
| ✅ Completado | 3 (Alta) | Vehículos, Palomares, Palomas, Conductores, Vuelos |
| ✅ Parcial | 4 (Muy alta) | Guardias (reducido a show/Hoy/pdf) — Pendiente: Users |
| ✅ Completado | 3 (Alta) | **Apoyos S-4** — CRUD TiposApoyo + CRUD Apoyos + Toggle Tabla/Calendario + Reporte por día (click en día → modal ops-panel) |
| ✅ Landing | — | 14 componentes (Hero, Navbar, Footer, Crucigrama, Tetris, Sudoku, SopaLetras, etc.) |

---

## Modelos clave, Policies y bloqueo de eliminación

**33 Policies** registradas en `AppServiceProvider` via `Gate::policy()`. **~181 permisos** en 32 módulos, 6 roles.

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

### 2026-08-21 (ADDENDUM compresión) — Fix ValueError "Path must not be empty" en subidas comprimidas: wrapper `ArchivoComprimido` + barrera `is_file()`

**Addendum de la entrada "Compresión automática de subidas" (más abajo). No se editó esa entrada.**

**Síntoma en producción IIS:** cualquier subida JPG que comprimiera >5% moría con HTTP 500:
`ValueError: fopen(): Argument #1 ($filename) must not be empty` / "Path must not be empty", con
frames `FilesystemAdapter.php:fopen` → `UploadedFile.php:putFileAs`. Reproducido 11/11 PASS en CLI.

**Causa raíz:** `envolverSiConviene()` devolvía un `Illuminate\Http\UploadedFile` **plano** sobre el
temporal de `tempnam()`. Ese objeto hereda `getRealPath()` de `SplFileInfo`, que **re-resuelve la ruta
vía `realpath()`** en cada llamada. En este servidor IIS `realpath()` devuelve `false` para
temporales recién escritos aunque el archivo exista (candidatos: barrido AV sobre `.tmp`, ACLs de la
identidad del app pool, 8.3 names deshabilitados — no identificado definitivamente). La cadena era:

```
storeAs → putFileAs → fopen($file->getRealPath(), 'r')  // getRealPath()=='' (false coercido)
        → fopen('') → ValueError → 500
```

Por eso la verificación previa en CLI pasó y QA no lo vio: en CLI sano `realpath()` resuelve bien;
la anomalía es del entorno IIS (simulable en CLI solo borrando el temporal entre wrap y store).

**Fix (2 archivos, SOLO el servicio — cero cambios en los 5 componentes Livewire):**

1. `app/Services/ArchivoComprimido.php` (NUEVO) — `final class ArchivoComprimido extends UploadedFile`
   que congela la ruta literal pasada al constructor y hace override de `getRealPath()` para
   devolverla SIEMPRE, sin pasar por `realpath()`. Con la ruta nunca vacía, `fopen(false)` es
   estructuralmente imposible. No necesita overrides extra: `getPathname()` (literal),
   `getSize()`/`getMimeType()`/`getClientOriginalName()`/`hashName()` funcionan sin cambios
   (verificado: W1-W9 PASS).
2. `app/Services/CompresorArchivos.php::envolverSiConviene()` — reemplazado `new UploadedFile(...)`
   por `new ArchivoComprimido($rutaFinal, $nombreOriginal)` + **barrera fail-open**: `is_file($rutaFinal)`
   justo antes de envolver; si falla, `Log::warning('CompresorArchivos: temporal inaccesible, se
   devuelve original sin comprimir.', [...])` y devuelve el ORIGINAL intacto (nunca el envuelto).
   Firma pública de `comprimir(): UploadedFile` SIN cambios; `ArchivoComprimido` ES un `UploadedFile`
   (subclase), así los type-hints/IDE/instanceof existentes siguen válidos.

**⚠️ Patrón para el futuro:** cuando se envuelva un archivo reescrito a un temporal del sistema,
NUNCA usar `UploadedFile` plano si el objeto va a atravesar `store()/storeAs()/putFileAs()` — el
`getRealPath()` heredado re-resuelve vía `realpath()` y en este IIS puede devolver false. Usar
`App\Services\ArchivoComprimido` o un override equivalente de `getRealPath()`.

**Nota API:** `League\Flysystem` corre con `strict_types`; el caso residual "temporal borrado entre
wrap y store" ya no lanza ValueError pero sí puede terminar en warning de `fopen` (ErrorException bajo
el handler de Laravel). Ventana de microsegundos e imposible tras la barrera `is_file()` salvo borrado
en pleno vuelo — mismo riesgo pre-existente, sin regresión.

**Verificación (script CLI con app booteada, 35 checks — 13 de regresión de la sesión anterior +
repro post-fix + contrato del wrapper + barrera): 35 PASS / 0 FAIL.**
- Repro escenario A post-fix: tras borrar el temporal + `clearstatcache` (anomalía simulada),
  `getRealPath()` devuelve LA MISMA ruta literal NO vacía (antes: `''`) → trigger del ValueError eliminado.
- Control B (condición real IIS: archivo existe, solo realpath falla): cadena completa
  `comprimir()->storeAs()` persiste en disco `guardias` con contenido íntegro.
- Regresión R1-R13: JPG 5.8MB→1.2MB lado ≤2000px; nombre/extensión/mime conservados;
  getSize()=filesize real; JPG ya optimizado sin crecimiento; PNG/ZIP/PDF pasan intactos;
  `puedeComprimirPdf()===false`; JPG corrupto fail-open; storeAs persiste bytes comprimidos íntegros.
- Barrera F1-F3: `is_file()` falla → original intacto (identidad, nunca `ArchivoComprimido`);
  `is_file()` ok → envuelve. Confirmación manual W2/W5/W6: `getPathname()`=ruta literal,
  `getMimeType()`=`image/jpeg` deducido del contenido, `getClientOriginalName()` conservado.
- `php -l` OK en ambos PHP. Suite Pest sigue NO ejecutable en este servidor (falta pdo_sqlite,
  limitación pre-existente documentada).

### 2026-08-21 — Compresión automática de subidas: JPG vía Intervention Image v4, PDF vía Ghostscript (detección runtime)

**Objetivo:** comprimir PDF/JPG/JPEG en el momento de la subida, antes de que el archivo llegue a su
storage final, reusando los puntos de guardado existentes (sin nuevo flujo de archivos).

**Investigación previa (verificada con codebase + grep):**
- Componentes con subida (`WithFileUploads`): `Documentos`, `GestionAdjuntos`, `novedades-guardia`
  (componente anónimo), `Vehiculos` (actas), `Landing/Contacto`, `Inventario/ItemsCatalogo`
  (este último solo xlsx/xls → fuera de alcance).
- `composer.json` NO tenía ninguna librería de imágenes → se instaló **intervention/image ^4.2**
  (quedó v4.2.0). Extensión GD disponible en el servidor (fileinfo también).
- Cero uso previo de Ghostscript/shell_exec/proc_open para PDFs (el único proc_open es
  BackupManager para backups). Symfony Process ya estaba en composer.json → se usa para invocar gs.

**⚠️ API Intervention Image v4 (NO es la v3 de la documentación vieja):**
| v3 | v4.2 |
|----|------|
| `new ImageManager('gd')` | `new ImageManager(\Intervention\Image\Drivers\Gd\Driver::class)` (FQCN, no string corto) |
| `$manager->read($path)` | `$manager->decodePath($path)` |
| `$image->toJpeg(75)` | `(string) $image->encode(new JpegEncoder(quality: 75, progressive: true))` |
| — | `orient()` y `scaleDown(w, h)` siguen existiendo |

**Servicio nuevo:** `app/Services/CompresorArchivos.php` — API estática única:
```php
$archivo = CompresorArchivos::comprimir($archivo); // UploadedFile -> UploadedFile
```
- **Fail-open garantizado:** NUNCA lanza ni rechaza; si la compresión falla devuelve el original
  intacto y loguea warning. La subida jamás se rompe por compresión.
- **JPG/JPEG:** corrige orientación EXIF (`orient()`), reduce a máx 2000px por lado
  (`scaleDown`), re-encodea JPEG progresivo calidad 78. Solo reemplaza si el resultado pesa
  ≤95% del original (nunca crece un archivo ya optimizado).
- **Guardia anti-OOM:** lee dimensiones con `getimagesize()` (barato) y NO decodifica imágenes
  >30MP — GD aloja ~4.3 bytes/píxel y un fatal de memoria NO es capturable.
- **PDF:** Ghostscript `-sDEVICE=pdfwrite -dCompatibilityLevel=1.5 -dPDFSETTINGS=/ebook`
  (150 dpi) vía Symfony Process, timeout 120s. Valida `%PDF` mágico + ahorro mínimo antes de usar.
- **Detección gs runtime:** Windows → glob `C:\Program Files\gs\gs*\bin\gswin*c.exe` +
  probe `gswin64c/gswin32c` en PATH; Unix → `gs`. Cacheada por proceso.
  Diagnóstico: `CompresorArchivos::puedeComprimirPdf()`.
- **Mecánica:** el contenido comprimido se escribe a un temp del sistema (fuera de livewire-tmp,
  no interfiere con la GC de Livewire) y se envuelve en un `UploadedFile` nuevo conservando el
  nombre original del cliente → los call-sites siguen usando `storeAs()/getSize()/
  getClientOriginalName()` sin ningún cambio.

**Ghostscript NO está instalado en este servidor IIS (verificado 2026-08-21: ni PATH ni Program
Files). TODO documentado en el docblock del servicio:** la compresión de PDF queda implementada
pero inactiva; al instalar Ghostscript (instalador oficial agrega gswin64c.exe al PATH, o
`apt install ghostscript`) NO hace falta cambiar código — la detección lo habilita sola.

**Componentes integrados (5) — patrón: comprimir justo antes del store final:**
| Componente | Punto de integración | Disco |
|------------|---------------------|-------|
| `Documentos.php` | `storeDocumento()` y `uploadNewFile()` (create y edit) | public |
| `GestionAdjuntos.php` | dentro del foreach de `subir()` | guardias |
| `novedades-guardia.php` | dentro del foreach al crear novedad | guardias |
| `Vehiculos.php` | `updatedSingleActaUpload()` antes de `store()` (actas aceptan pdf/jpg/jpeg/png/bmp/doc/docx) | public |
| `Landing/Contacto.php` | `enviarSugerencia()` antes de `store()` (reduce también el peso del mail) | public |

Efectos colaterales correctos: `tamanio`/`file_size` quedan grabados con el tamaño COMPRIMIDO
(usen el `$archivo` retornado, no el original); el thumbnail de Documentos sigue generándose
(mime image/jpeg preservado); las validaciones de tamaño corren ANTES de comprimir (son techos,
comprimir solo achica).

**Fuera de alcance (deliberado):** PNG/GIF/BMP (recompresión rara vez ayuda y complica
transparencia), ItemsCatalogo (Excel), thumbnails de Documentos.

**Verificación (suite Pest NO ejecutable en este servidor — falta pdo_sqlite, limitación
pre-existente):**
- `php -l` OK en los 6 PHP (servicio + 5 componentes).
- Script CLI con app booteada: **13/13 PASS** — JPG grande 188KB→18KB con lado máx 2000px;
  nombre/extensión/mime conservados; JPG chico sin crecimiento; PNG/ZIP pasan intactos;
  PDF pasa intacto con `puedeComprimirPdf() === false`; JPG corrupto fail-open;
  `putFileAs` del Filesystem de Laravel sobre el archivo comprimido OK.
- `php artisan view:cache` compila todos los blades sin errores (+ `view:clear`).
- Pint: el repo YA fallaba `pint --test` en código commiteado pre-existente (baseline verificado
  sobre NovedadService/Apoyos); no se corrió fix para no ensuciar el diff.

**Patrón para futuros componentes con subida:** después de validar y antes del
`storeAs()/store()`: `$archivo = CompresorArchivos::comprimir($archivo);`. Nada más.

### 2026-08-21 — 'zip' permitido en subidas: reemplazo de `mimes:` por validación por extensión (regla custom `ExtensionPermitida`)

**Objetivo:** permitir archivos .zip en los componentes que validan subida con `mimes:`. La regla
`mimes:` de Laravel valida por mimetype detectado (`guessExtension()`), y con ZIP los navegadores
envían mimetypes dispares (`application/zip` vs `application/x-zip-compressed` vs
`application/octet-stream`), lo que hace que archivos zip válidos fallen la validación de forma
intermitente según el navegador. Solución: validar por **extensión del nombre original**
(`getClientOriginalExtension()`), inmune a la discordancia de mimetypes.

**Regla nueva (1 archivo creado):**
- `app/Rules/ExtensionPermitida.php` — implementa `Illuminate\Contracts\Validation\ValidationRule`
  (regla invokable). Constructor recibe el array de extensiones permitidas (normaliza a lowercase).
  Rechaza con mensaje "El formato del archivo no está permitido. Formatos válidos: ...". Guard
  `instanceof UploadedFile` verificado contra `Livewire\Features\SupportFileUploads\TemporaryUploadedFile`
  (SÍ es subclase de `Illuminate\Http\UploadedFile` → funciona con uploads de Livewire).

**Componentes migrados (4) — patrón `['file', new ExtensionPermitida([...]), 'max:...']`:**
| Componente | Antes | Ahora |
|------------|-------|-------|
| `app/Livewire/Documentos.php` | `mimes:` + `$allowedMimes` (14 ext) | `$allowedExtensions` (+`zip`, 15 ext); propiedad renombrada (era private, 0 impacto externo); entry `formArchivo.mimes` removido de `mensajesValidacion()` (el mensaje lo lleva la regla) |
| `app/Livewire/GestionAdjuntos.php` | `mimes:pdf,jpg,jpeg,png` | `ExtensionPermitida(['pdf','jpg','jpeg','png','zip'])` |
| `resources/views/components/novedades-guardia/novedades-guardia.php` | ídem | ídem |
| `app/Livewire/Landing/Contacto.php` | `mimes:txt,pdf,doc,docx,jpg,jpeg,png,gif` (5MB público) | `ExtensionPermitida([..., 'zip'])`; se conserva `max:5120` (5MB deliberado por superficie de abuso); entry `sugerencia_adjunto.mimes` removido |

**Blades actualizados (4):** `accept=` ahora incluye `.zip` en gestion-adjuntos.blade.php,
novedades-guardia.blade.php, documentos/index.blade.php y contacto.blade.php (este último también
sumó `.jpeg` que faltaba). Textos de UI: label de gestión de adjuntos "(PDF, JPG, PNG, ZIP — máx.
50MB c/u)" y ayuda de Documentos "Formatos: PDF, DOC, DOCX, TXT, ZIP y más".

**NO se tocó (deliberado):**
- `Inventario/ItemsCatalogo.php` — `archivoExcel` `mimes:xlsx,xls`: es un importador de Excel; un
  zip rompería el import con error críptico. El zip no aplica semánticamente.
- `Http/Requests/StoreDocumentoRequest.php` — sigue huérfano (0 referencias, ver entrada 2026-08-21
  de límites de subida); no es componente Livewire.

**Trade-off de seguridad documentado:** validar por extensión confía en el nombre del cliente
(a diferencia del sniffing de contenido de `mimes:`). Mitigaciones vigentes: la regla `file` se
conserva (valida upload HTTP real), los tamaños siguen limitados, y los nombres se regeneran al
almacenar (Documentos usa `Str::slug`+timestamp; GestionAdjuntos/novedades usan `time()+uniqid()`).

**Verificación (suite Pest NO ejecutable en este servidor — falta pdo_sqlite, limitación pre-existente):**
- `php -l` OK en los 5 PHP (regla nueva + 4 componentes).
- `php artisan view:cache` compila todos los blades sin errores (+ `view:clear`).
- Script CLI con `Validator` real: 9/9 checks PASS — `.zip` y `.ZIP` pasan; `.zipx`, `.exe`, `.php`
  rechazados con mensaje listando formatos; jpg/pdf siguen pasando; contenido PK sniffado distinto
  no afecta (la extensión manda); `TemporaryUploadedFile instanceof Illuminate\Http\UploadedFile` = true.

**Patrón para futuros componentes con subida:** usar `new ExtensionPermitida(['ext1', 'ext2', ...])`
en lugar de `mimes:` cuando la lista incluya zip (o directamente siempre, por consistencia). La regla
está en `App\Rules\ExtensionPermitida` y acepta cualquier array de extensiones.

### 2026-08-21 — Límite de subida de archivos: 10MB → 50MB consistente en toda la cadena (PHP → IIS → Livewire → componentes)

**Síntoma:** al subir un archivo mayor a 10MB no se dispara NINGÚN mensaje de validación — la subida
falla en silencio.

**Diagnóstico (cadena de rechazo silencioso, 4 capas ANTES de las reglas de los componentes):**
1. **PHP SAPI web** (`upload_max_filesize`/`post_max_size`): si el POST a `/livewire/upload-file`
   excede el límite, PHP lo rechaza (request vacío → `PostTooLargeException` → HTTP 413).
2. **IIS `maxAllowedContentLength`**: default ~28.6MB cuando no está definido en web.config →
   HTTP 404.13 antes de llegar a PHP. `public/web.config` NO lo definía.
3. **Reglas del endpoint temporal de Livewire** (`config/livewire.php` →
   `temporary_file_upload.rules`): estaba en `null` → default Livewire `max:12288` (12MB).
4. **Evento `upload-error`:** ante cualquier rechazo de las capas 1-3, Livewire JS dispara el
   evento `upload-error`, que **nadie escuchaba en todo el proyecto** (verificado con grep:
   0 handlers). Resultado: silencio total.

Las reglas `max:10240` de los componentes corren DESPUÉS (cuando el archivo ya llegó a
`livewire-tmp/`) → para archivos rechazados en las capas 1-3 eran inalcanzables y por eso
nunca producían mensaje.

**Estado php.ini verificado en este servidor (producción IIS):**
- IIS FastCGI usa `C:\php8.4\php-cgi.exe` (applicationHost.config) → carga `C:\php8.4\php.ini`,
  el MISMO del CLI. Valores YA correctos: `upload_max_filesize=50M`, `post_max_size=55M`,
  `memory_limit=256M`. No hizo falta tocarlo.
- **Laragon local (otra máquina):** ajustar en `C:\laragon\bin\php\php-<versión>\php.ini`:
  `upload_max_filesize=50M` + `post_max_size=55M` (post ≥ upload; margen 5MB para el resto del
  request) y reiniciar Apache desde el menú de Laragon. Verificar con `php -i | grep max_filesize`
  o un `phpinfo()` vía web (el ini del CLI puede diferir del del Apache módulo).

**Cambios aplicados (8 archivos):**
- `config/livewire.php` — `temporary_file_upload.rules`: `null` → `'file|max:51200'` (KB = 50MB).
  Este cambio ya estaba en el working directory sin commitear de una sesión previa; se conserva.
- `app/Livewire/Documentos.php` — `formArchivo`: `max:10240` → `max:51200` (create y edit) +
  mensaje custom "El archivo no puede superar los 50 MB."
- `app/Livewire/GestionAdjuntos.php` — `archivos.*`: `max:10240` → `max:51200`.
- `resources/views/components/novedades-guardia/novedades-guardia.php` — `archivos.*`:
  `max:10240` → `max:51200`.
- `app/Livewire/Vehiculos.php` — actas de vehículo (validación manual en bytes): individual
  `10485760` → `52428800`, total entre archivos `10485760` → `52428800` (en `updatedSingleActaUpload`
  y en `guardar()`) + mensajes "10MB" → "50MB".
- `public/web.config` — agregado `<security><requestFiltering><requestLimits
  maxAllowedContentLength="57671680" />` (55MB) para cubrir `post_max_size=55M`. Sin esto IIS
  rechaza >28.6MB con 404.13 aunque PHP esté bien. Si se sube el límite de nuevo, actualizar AMBOS.
- Textos de UI actualizados a 50MB: `novedades-guardia.blade.php` ("max: 50MB c/u"),
  `gestion-adjuntos.blade.php`, `documentos/index.blade.php`, `vehiculos/index.blade.php`
  (incluye la constante JS client-side `maxBytes = 50 * 1024 * 1024` que pre-valida antes de
  `$wire.upload()`).

**NO se tocó (límites menores deliberados, funcionan correctamente porque ahora la cadena los deja llegar):**
- `Landing/Contacto.php` — `sugerencia_adjunto` `max:5120` (5MB): formulario PÚBLICO sin auth,
  se mantiene chico por superficie de abuso.
- `Inventario/ItemsCatalogo.php` — `archivoExcel` `max:5120` (5MB): import xlsx/xls.

**Hallazgos colaterales (sin tocar, documentados):**
- `app/Http/Requests/StoreDocumentoRequest.php` es código huérfano (0 referencias vía grep, mismo
  patrón que los controllers eliminados) y además tiene un bug latente: `max:10485760` son KILOBYTES
  (= 10GB reales), el comentario dice "10MB máx". Pendiente: eliminar o corregir si algún día se usa.
- Ventana residual silenciosa: archivos >55MB siguen siendo rechazados por PHP/IIS sin mensaje
  (el evento `upload-error` sigue sin handlers). Mejora futura: agregar `#[On('upload-error')]`
  o `$wire.on('upload-error', ...)` en los componentes con subida para mostrar toast.

**Verificación (suite Pest NO ejecutable en este servidor — falta pdo_sqlite, solo pdo_mysql;
limitación pre-existente documentada):**
- `php -l` OK en los 4 archivos PHP editados.
- `simplexml_load_file(web.config)` → XML OK.
- `php artisan tinker` → `config('livewire.temporary_file_upload.rules')` = `file|max:51200`;
  `ini_get` = 50M / 55M.
- `php artisan view:cache` compila todos los blades sin errores (+ `view:clear`).
- Grep final: 0 referencias restantes a `max:10240`/`10485760`/`10MB` en contexto de subida
  (solo quedan las intencionales: Contacto/ItemsCatalogo 5MB y un comentario de doc de un Job).

### 2026-08-21 — Pantalla de administración "Unidades por Módulo" (matriz Unidades × Módulos con toggle instantáneo)

**Objetivo:** cerrar el pendiente de la entrada anterior — editar las listas curadas de la pivot
`unidad_modulo` desde una pantalla, sin correr `UnidadModuloSeeder` a mano.

**Componente Livewire:** `app/Livewire/Admin/UnidadesModulos.php` (namespace `App\Livewire\Admin`,
convención de paneles admin). Sin paginación: matriz completa de todas las unidades (activas e
inactivas) × las 9 claves de `UnidadModulo::MODULOS`.

**Funcionalidad:**
- Matriz con checkboxes por celda; cada checkbox es un switch instantáneo (`wire:click="toggle(unidadId, 'modulo')"`,
  sin botón Guardar). `toggle()` crea o borra la fila del pivot y hace `unset($this->pivotes)` para
  invalidar el computed → re-render refleja el estado real.
- Feedback vía toasts (`successMsg`/`errorMsg` + watchers `$wire.$watch` → `mostrarToast`, patrón Fase 2).
- Validaciones en `toggle()`: módulo fuera de `UnidadModulo::MODULOS` → errorMsg; unidad inexistente → errorMsg.
- **Columna "Usos"** (contexto informativo, NO bloquea): contador de registros guardados que referencian
  cada unidad hoy. Fuentes: Users, Vehículos, Comisiones, Pases, Novedades de Rancho (belongsTo directo,
  una query agrupada `groupBy('unidad_id')` por tabla) + Apoyos S-4 (pivot `apoyo_unidad` JOIN `apoyos`
  con `whereNull('apoyos.deleted_at')`). Tooltip con desglose ("33 Pases · 32 Usuarios · ...").
  Quitar una unidad de un módulo NO borra estos datos — solo afecta selectores futuros.
- Leyenda al pie con clave técnica + etiqueta + descripción de cada selector.
- Nota visible: unidades Inactivas nunca aparecen en selectores aunque estén tildadas (el scope
  `curadasPara` exige `activo=true`); la casilla se conserva para si se reactiva.

**Etiquetas legibles:** nueva const `UnidadModulo::ETIQUETAS` (en paridad con MODULOS) — ej.
`usuarios_alta` → "Alta de Usuario", `guardias_rancho` → "Rancho de Guardia", `apoyos_asignacion` →
"Asignación de Apoyos". Las descripciones largas viven solo en la vista (@php $descripciones).

**Policy:** `app/Policies/UnidadModuloPolicy.php` — `viewAny` + `update`, ambos con el permiso único
`gestionar_unidades_modulo`. SuperAdmin exento vía `Gate::before` global. Registrada en
AppServiceProvider (`Gate::policy(UnidadModulo::class, UnidadModuloPolicy::class)`) + gate de sidebar
`Gate::define('viewAny-unidades-modulo', isAdmin || HasPermisos(...))`.

**Permiso nuevo:** `'UnidadModulo' => [gestionar_unidades_modulo]` agregado a PermisoSeeder (módulo
propio, junto a 'Unidad'). Ejecutado `php artisan db:seed --class=PermisoSeeder` (updateOrCreate,
no toca roles) + asignado al rol admin vía `syncWithoutDetaching` (NO RolSeeder, que haría sync()
destructivo). Total permisos: 223 → 224.

**Ruta:** `GET /admin/unidades/modulos` → `livewire.admin.unidades-modulos.layout`, name
`admin.unidades.modulos.index`. Registrada ANTES del resource `unidades/{unidad}` (mismo patrón que
`/apoyos/tipos` antes de `apoyos/{apoyo}`) para no ser capturada por `unidades.show`.

**Sidebar:** entrada "Unidades por Módulo" (fa-solid fa-table-cells) dentro de "Guardias y Novedades",
debajo de "Unidades Ámbito". Ajuste menor: `active` de "Unidades Ámbito" pasó de `['admin/unidades*']`
a `['admin/unidades']` (exacto) para que no queden ambos resaltados al visitar /admin/unidades/modulos.

**Archivos creados (4):**
- `app/Livewire/Admin/UnidadesModulos.php`
- `app/Policies/UnidadModuloPolicy.php`
- `resources/views/livewire/admin/unidades-modulos/layout.blade.php`
- `resources/views/livewire/admin/unidades-modulos/index.blade.php`

**Archivos modificados (5):**
- `app/Models/UnidadModulo.php` — const ETIQUETAS (+18 líneas)
- `app/Providers/AppServiceProvider.php` — imports + Gate::policy + Gate::define
- `database/seeders/PermisoSeeder.php` — módulo 'UnidadModulo'
- `routes/web.php` — ruta /admin/unidades/modulos
- `config/adminlte.php` — sidebar + fix active de "Unidades Ámbito"

**Verificación (script CLI + transacción/rollback, users=50/unidades=5/pivot=42/permisos=223→224
antes=después salvo el permiso nuevo intencional):**
15 checks via `Livewire::test()` + HTML renderizado, todo PASS:
- Ruta registrada (`admin.unidades.modulos.index` → /admin/unidades/modulos).
- Render superadmin: 45 checkboxes exactos (5 unidades × 9 módulos), 42 marcados (= filas del pivot).
- Las 9 etiquetas legibles presentes en headers; badges Activa/Inactiva + badge Usos con tooltip desglose.
- Baseline `curadasPara('guardias_rancho')` = 5 unidades → toggle OFF: fila pivot borrada, scope devuelve
  4 y excluye la unidad EN EL MISMO REQUEST (computed invalidado) → toggle ON: restaurada, scope vuelve a 5.
- Módulo inválido ignorado (errorMsg, pivot intacta); unidad inexistente ignorada.
- `usosPorUnidad`: estructura correcta, totales reales (#1=77 #2=22 #3=23 #4=14 #5=4).
- Usuario sin `gestionar_unidades_modulo`: mount bloqueado con 403 "This action is unauthorized",
  matriz NO renderizada. ⚠️ Nota API: en Livewire 4, `Livewire::test()` NO lanza la
  AuthorizationException de mount como excepción PHP — renderiza página de error completa;
  asertar chequeando el HTML (403 + ausencia del contenido), no con try/catch.
- `php artisan view:cache` compila todos los blades sin errores.
- Suite Pest NO ejecutable en este servidor (falta pdo_sqlite, limitación pre-existente documentada).

**Pendiente visual:** confirmar en navegador (/admin/unidades/modulos) que tildar/destildar refleja el
cambio inmediato en los selectores (ej. Rancho de Guardia en guardia abierta).

### 2026-08-21 — Listas curadas de unidades por módulo (pivot unidad_modulo + scope curadasPara)

**Objetivo:** reemplazar el patrón repetido `Unidad::where('activo', true)` (con exclusiones ad-hoc
por nombre `'C.A.C.O.'` hardcodeadas en 3 lugares) por **listas curadas por módulo** gestionadas en BD.
Comportamiento visible 100% idéntico al previo (verificado).

**Nuevo schema (migración):**
- `database/migrations/2026_08_21_000000_create_unidad_modulo_table.php` — tabla pivot `unidad_modulo`
  (`unidad_id` FK cascade → unidades, `modulo` string, timestamps, unique `(unidad_id, modulo)`,
  index en `modulo`). Schema Builder estándar → compatible MySQL/Postgres/SQLite.

**Modelo nuevo:**
- `app/Models/UnidadModulo.php` — const `MODULOS` (fuente única de verdad de las claves válidas),
  relación `unidad()`.

**Scope nuevo en `Unidad`:**
```php
Unidad::curadasPara(string $modulo) // activo=true AND EXISTS(pivot con ese módulo), orderBy nombre
```
Relación soporte: `Unidad::unidadModulos()` hasMany. Claves válidas (`UnidadModulo::MODULOS`):

| Clave | Selector | Antes |
|-------|----------|-------|
| `usuarios_alta` | UserWizard (computed unidades) | activo=true |
| `usuarios_edicion` | UserForm render (conserva `orWhere(id)` para la unidad asignada aunque no esté curada/inactiva) | activo=true |
| `usuarios_registro` | FortifyServiceProvider registerView | activo=true − C.A.C.O. (exclusión estaba en register.blade.php:83) |
| `vehiculos_form` | Vehiculos@catalogos['unidades'] | activo=true − C.A.C.O. (exclusión estaba en vehiculos/index.blade.php:327) |
| `vehiculos_tabs` | Vehiculos@render unidadesTabs | activo=true − C.A.C.O. (en query) |
| `guardias_rancho` | GuardiaController@show | activo=true (C.A.C.O. SÍ incluida — correcto) |
| `apoyos_asignacion` | Apoyos@unidadesDisponibles ("A quien se dispuso") | activo=true |
| `pase` | PasePanel@unidades | activo=true |
| `comision` | ComisionPanel@unidades | activo=true |

Fuera de alcance (NO tocar): "Unidades de destino" de Expedidos en Guardias consulta `organismos`,
no `unidades`.

**Seeder:** `database/seeders/UnidadModuloSeeder.php` (registrado en DatabaseSeeder después de
UnidadSeeder). Reproduce EXACTAMENTE el estado previo: todas las unidades activas en los 9 módulos,
excepto C.A.C.O. en `usuarios_registro`, `vehiculos_form`, `vehiculos_tabs` (const `SIN_CACO`).
Idempotente (firstOrCreate) — puede re-correrse y toma unidades nuevas creadas a futuro.

**Estado actual BD:** 42 filas pivot (9 módulos × 5 unidades − 3 exclusiones).

**Verificación (13 checks CLI, todo PASS):**
- Los 9 selectores devuelven lista IDÉNTICA al baseline capturado antes del refactor
  (misma cantidad, mismos nombres, mismo orden alfabético).
- Scope respeta `activo=false` (unidad inactiva con pivot NO aparece) y exige pivot
  (unidad activa sin pivot NO aparece).
- UserForm: `orWhere(id)` conserva la unidad asignada fuera de la lista curada (semántica intacta).
- Integridad BD antes=después (users=51, apoyos=2, tipos_apoyo=4, unidades=5).
- `php artisan view:cache` compila todos los blades sin errores (los 2 edits Blade son seguros).
- Suite Pest NO ejecutable en este servidor (falta pdo_sqlite, limitación pre-existente documentada);
  ningún test referenciaba los selectores/exclusiones modificados.

**Pendiente:** pantalla de administración para editar `unidad_modulo` sin seeder (decisión futura);
al crear/desactivar una unidad nueva, re-correr `php artisan db:seed --class=UnidadModuloSeeder`.

### 2026-08-20 — Apoyos S-4: REVERTIDO el intento de form inline — el form Nuevo/Editar Apoyo vuelve al modal ops-panel

**⚠️ Estado:** El cambio "formulario convertido de modal a INLINE" (ver changelog previo en historial
de git) se aplicó por error y fue **REVERTIDO**. El formulario de crear/editar Apoyo vuelve a ser un
**modal ops-panel** (`x-teleport="body"`, overlay con backdrop, centrado, `wire:click.self="cerrarForm"`,
pantalla de éxito `@if ($justSaved)` dentro del modal) — mismo patrón que TiposApoyo y el resto del proyecto.

**Cómo se revertió (importante para futuros reverts):**
- El cambio inline **NO estaba commiteado** (working directory sucio sobre `a28ef84`). No aplicó `git revert`.
- Se ejecutó `git restore app/Livewire/Apoyos.php resources/views/livewire/apoyos/index.blade.php`
  para volver al estado commiteado (modal + form apilado).
- **PERO:** la grilla Bootstrap del form (changelog siguiente) TAMPOCO estaba commiteada — vivía solo
  en el working directory y se perdió con el restore. Fue **reconstruida a mano** dentro del modal,
  siguiendo la especificación del changelog de abajo (7 filas, cada una su `.row`).
- Lección: `git restore` es destructivo para cambios no commiteados; antes de restaurar, verificar con
  `git log --follow <archivo>` qué hay realmente en HEAD vs working directory.

**Fix incluido (defecto latente del código original commiteado):** `$justSaved` nunca se reseteaba,
así que tras guardar con éxito, reabrir el modal ("Nuevo" o "Editar") mostraba la pantalla de éxito
en vez del formulario. Ahora `crear()` y `abrirEditar()` resetean `$justSaved = false` (+ `$errorMsg = ''`)
al abrir. El flujo guardar → pantalla de éxito → cerrar → reabrir muestra el form correctamente.

**Archivos modificados (2):**
- `app/Livewire/Apoyos.php` — restaurado a HEAD (dispatchs `abrir-modal-apoyos`/`cerrar-modal-apoyos`,
  propiedad `$justSaved`, sin toggle en `crear()`) + fix de reset de `$justSaved`/`$errorMsg` en
  `crear()` y `abrirEditar()` (+8 líneas).
- `resources/views/livewire/apoyos/index.blade.php` — restaurado a HEAD (modal `#modalApoyos` completo)
  + grilla de 7 filas reconstruida dentro del `<form id="form-apoyo">` (ver entrada siguiente).

**No tocado:** `TiposApoyo.php` y `apoyos/tipos/index.blade.php` — ⚠️ OJO: estos archivos TODAVÍA tienen
la conversión inline aplicada SIN commitear (mismo patrón que se revirtió en Apoyos). Quedan pendientes
de decisión: revertir igual o mantener.

**Verificación (script CLI + transacción/rollback, users=50/apoyos=1/tipos=3 antes=después):**
53 checks via `Livewire::test()` + HTML renderizado, todo PASS:
- HTML: `id="modalApoyos"` + `x-teleport="body"` + `ops-panel-overlay` presentes; `form-apoyo` DENTRO
  del bloque del modal; watcher `$watch('$wire.showForm')` + `:class is-open`; submit con
  `form="form-apoyo"` desde el footer.
- Grilla: filas 1-7 con labels esperados, `col-md-6` en pares, `ms-wrapper` del multi-select presente.
- `crear()` x2 sigue abierto (patrón modal, NO toggle). Validación fallida → modal permanece abierto.
- CREATE exitoso: fila en BD + unidades sync + `registrado_por_id` + pantalla de éxito (`justSaved=true`)
  dentro del modal.
- EDIT tras CREATE: `abrirEditar` resetea `justSaved` (muestra el FORM, no el éxito previo), carga
  tipo/unidades/datos, `guardar()` persiste y deja `justSaved=true`.
- Vista calendario + confirm-delete operativos. Integridad BD verificada (counts idénticos).

**Notas API Livewire 4 para scripts CLI (costosas de descubrir):**
- `actingAs()` es ESTÁTICO: `Livewire\Livewire::actingAs($user)->test(Component::class)`.
- `$t->errors()->has('campo')` — `hasError()` NO existe en Testable.
- Los params de `call()` son POSICIONALES: `$t->call('abrirEditar', $id)` — pasar `['id' => $id]`
  lanza TypeError (`array given`) que con `app.debug=false` se enmascara como HTTP 419; la página de
  error 419 renderiza el layout AdminLTE (con `notificaciones-watcher` adentro) y `$t->instance()`
  pasa a devolver ESE componente (PropertyNotFoundException confusa). Diagnosticar con
  `config(['app.debug' => true])` en el proceso CLI.

### 2026-08-20 — Apoyos S-4: formulario Nuevo/Editar Apoyo reorganizado en grilla Bootstrap

> **Nota post-revert:** esta grilla se aplicó originalmente en el working directory junto con la
> conversión inline (ambos cambios sin commitear). El `git restore` del revert la eliminó y fue
> **reconstruida a mano** dentro del modal restaurado según la especificación de abajo. La versión
> actual (modal + grilla) es la que quedó vigente.

**Layout only:** El form del modal ops-panel (`resources/views/livewire/apoyos/index.blade.php`,
bloque `<form id="form-apoyo">`) pasó de columnas apiladas a grilla responsiva `row`/`col-md-*`
(mismo grid que el resto del proyecto). Cero cambios de lógica: mismos wire:model, validaciones,
condicionales y modal (ops-panel/backdrop/ancho intactos).

**Estructura de filas:**
| Fila | Columnas |
|------|----------|
| 1 | Tipo de apoyo \| Solicitante (col-md-6) |
| 2 | Documento buscador \| Documento texto libre (col-md-6, texto libre respeta su `@if (!$formDocumentoNovedadId)`) |
| 3 | Desde \| Hasta (col-md-6 — ya existía, no se tocó) |
| 4 | Por Documento buscador \| Por Documento texto libre (col-md-6, ídem condicional) |
| 5 | A quien se dispuso multi-select (col-12) |
| 6 | Estado solo (col-md-6, segunda columna vacía — "Registrado por" NO se muestra en el form, es automático al crear) |
| 7 | Descripción textarea (col-12) |

**Detalles:**
- Cada fila es un `.row` propio (NO un único row con cols corridos): si un texto libre se oculta
  por el condicional de documento seleccionado, la fila colapsa a una sola col-md-6 sin arrastrar
  campos de la fila siguiente.
- Único cambio de copy: helper del buscador "escribí el texto libre **abajo**" → "**completá el
  campo de texto libre**" (en desktop ya no queda abajo sino a la derecha; wording neutral para
  ambos breakpoints).
- Mobile: apilado estándar Bootstrap (<768px los col-md-* vuelven a full-width). Sin overrides de
  `.row`/`.col-*` en ops-panel.css (verificado). `.ms-wrapper` ya tenía `position:relative`, el
  dropdown del multi-select no cambia de contexto.

**Verificación (script CLI + transacción/rollback, users=50 antes=después):**
20 checks via `Livewire::test()` + DOMDocument/XPath sobre el HTML renderizado, todo PASS:
- CREATE: 7 rows exactas con cols/labels esperados por fila.
- EDIT (apoyo real con documento seleccionado): fila 2 colapsa a 1 col (condicional OK).
- COND (setear formDocumentoNovedadId en create): texto libre oculto, 7 filas preservadas.
- Nota API Livewire 4 para scripts CLI: `actingAs()` es ESTÁTICO —
  `Livewire\Livewire::actingAs($user)->test(Component::class)`, no `->test(...)->actingAs($user)`
  (devuelve null y revienta con "call() on null").

**Pendiente visual:** confirmar en navegador desktop/mobile (/admin/apoyos → Nuevo Apoyo).

### 2026-08-20 — Apoyos S-4: reporte por día (click en día del calendario → modal ops-panel)

**Funcionalidad (Fase 4b):** Al hacer click en un día del calendario que tenga al menos un apoyo,
se abre un modal ops-panel (`x-teleport="body"`, mismo patrón que el form) con el detalle de los
apoyos de ese día. Días sin apoyos: sin click, cursor normal.

**Componente (`app/Livewire/Apoyos.php`):**
- Propiedad `?string $diaSeleccionado = null` (string 'Y-m-d', null = modal cerrado)
- `seleccionarDia(string $fecha)` — valida el formato antes de setear; fecha inválida se ignora
- `cerrarReporteDia()` — limpia la selección (overlay self-click + botón Cerrar)
- `#[Computed] apoyosDelMes()` — **la query mensual se movió aquí**; `apoyosPorDia()` ahora la
  consume (`$apoyos = $this->apoyosDelMes`) con su lógica de agrupamiento intacta. Eager loads
  ampliados a `['tipo', 'organismo', 'unidades']` para el reporte. Resultado: UNA sola query por
  request compartida entre grilla y modal (verificado con query log: abrir el modal dispara 0 queries nuevas).
- `#[Computed] apoyosDelDiaSeleccionado()` — filtra EN PHP sobre `apoyosDelMes` (sin reconsultar),
  comparando por día truncado (`desde.startOfDay() <= dia <= hasta.startOfDay()`), ordenado por tipo+id
- `tituloDiaSeleccionado()` — "Lunes 24 de agosto de 2026" vía `translatedFormat('l j \d\e F \d\e Y')`
  + ucfirst manual (no existe `mb_ucfirst`)
- `posicionEnRango(Apoyo $apoyo): ?array` — devuelve `['actual' => X, 'total' => Y]` para apoyos
  multi-día, null si es de un solo día. Usa `diffInDays` con `(int) round(...)` (Carbon 3 lo devuelve float)
- Helper privado estático `parsearFechaDia(?string): ?Carbon` — **Carbon 3: `createFromFormat()` LANZA
  excepción ante entrada inválida, no devuelve false** como `DateTime::createFromFormat`. Todos los
  parseos de `$diaSeleccionado` pasan por este helper (protege contra tampering vía `wire:set` directo)
- `abrirEditar()` ahora hace `$this->diaSeleccionado = null;` — al editar desde el reporte se cierra
  ese modal antes de abrir el form (evita overlays apilados)

**Vista (`resources/views/livewire/apoyos/index.blade.php`):**
- Celdas de día con apoyos: `wire:click="seleccionarDia('YYYY-MM-DD')"` + `title` tooltip.
  Fecha generada con `sprintf('%04d-%02d-%02d', ...)`. Días vacíos/offset: sin click
- Modal nuevo `#modalReporteDia`: watcher Alpine sobre `$wire.diaSeleccionado` toggling
  `ops-panel-open` en body + `:class="{ 'is-open': ... }"` + `wire:click.self="cerrarReporteDia"`
- Cada apoyo en card: swatch color tipo, Solicitante, Período Desde—Hasta, badge "día X de Y"
  (solo multi-día), unidades en badges, Descripción, estado badge, footer con botón Editar
  (`@can('update', $apoyo)`)

**CSS (`public/css/ops-panel.css`):** hover reforzado en `.apoyos-calendar__day--has:hover`
(inset ring rgba(11,37,69,.18) + day-num #0B2545). El cursor pointer ya existía.

**Verificación (sin suite Pest — este servidor no tiene pdo_sqlite):**
Script CLI con transacción + rollback (cero residuo: users=50, apoyos=1, tipos=3, unidades=5
antes y después). 50 checks via `Livewire::test()` (ciclo de vida completo), todos PASS:
- Día con 1 apoyo propio + otro de tipo distinto → 2 cards, swatches, badges, unidades, descripción
- Apoyo multi-día 23→27 sep → posiciones 1..5 de 5 correctas en cada día; indicador "día 3 de 5" en HTML
- Día gap (22) y día sin apoyos (10) → colección vacía + empty-state
- Reuso: warm cache `apoyosPorDia` → acceder `apoyosDelDiaSeleccionado` dispara 0 queries
- Robustez: 'garbage-input' ignorado, tampering `wire:set('diaSeleccionado','basura')` no explota
- Editar desde modal → cierra reporte, abre form con datos y unidades correctas
- Smoke con dato real (apoyo 24→29 ago): "día 1 de 6", título "Lunes 24 de agosto de 2026"

### 2026-08-20 — Apoyos S-4: fix loop infinito en vista Calendario (HTTP 500 FastCGI timeout)

**Síntoma:** Al cambiar a vista Calendario en `/admin/apoyos`, el servidor devolvía HTTP 500
"El proceso FastCGI superó el tiempo de espera de la actividad configurada". Timeout real de PHP.

**Causa raíz (2 bugs encadenados):**

1. **Loop infinito por CarbonImmutable** (`app/Livewire/Apoyos.php` — `apoyosPorDia()`):
   `AppServiceProvider::configureDefaults()` ejecuta `Date::use(CarbonImmutable::class)`, por lo que
   TODOS los casts `datetime` de Eloquent devuelven `Carbon\CarbonImmutable`. El loop del calendario
   hacía `$current->addDay();` como *statement*: con Carbon mutable eso avanza el objeto, pero con
   **CarbonImmutable devuelve una instancia nueva que se descarta** y `$current` queda congelado →
   `while ($current->lte($diaFin))` nunca se vuelve falso → loop infinito.
   - Los datos de BD estaban bien (1 apoyo, rango razonable de 6 días). No era un problema de datos.
   - Solo explotaba en vista Calendario porque el loop solo corre ahí (`apoyosPorDia` computed).
   - Podía parecer intermitente: si el apoyo empezaba antes del día 1 del mes, `$diaInicio` tomaba
     `$inicioMes->copy()` (mutable vía `Carbon::create`) y el loop terminaba.

2. **`isoLocale()` eliminado en Carbon 3** (`nombreMesActual()`): estaba enmascarado por el loop
   infinito (el proceso moría antes de llegar al render de la vista). `isoLocale('es')` no existe en
   nesbot/carbon 3.x — reemplazado por `->locale('es')->translatedFormat('F Y')`.

**Fix aplicado (`app/Livewire/Apoyos.php`):**
```php
// ANTES (loop infinito con CarbonImmutable):
$current->addDay();

// DESPUÉS (compatible mutable + immutable):
$current = $current->addDay();
```
Más salvaguarda defensiva: si el loop supera 366 iteraciones por apoyo, corta y loguea
`Log::warning('Apoyos@apoyosPorDia: rango de fechas excesivo, loop cortado.', [...])` con
apoyo_id/desde/hasta — un dato mal cargado a futuro no vuelve a colgar el servidor.

**⚠️ Patrón crítico para TODO el proyecto (Date::use(CarbonImmutable::class) activo):**
Los casts `datetime` de Eloquent devuelven **CarbonImmutable**. NUNCA usar `->addDay()`,
`->subMonth()`, `->startOfDay()`, etc. como statement esperando mutación sobre fechas que vienen
de un modelo. Siempre reasignar: `$fecha = $fecha->addDay();`. Los objetos creados vía
`\Carbon\Carbon::parse()` / `Carbon::create()` / `createFromFormat()` siguen siendo mutables
(`Date::use` solo afecta al factory de Laravel: casts, helper `now()`, facade `Date`).

**Verificación (sin suite Pest — este servidor no tiene pdo_sqlite, ver Pendiente):**
- Reproducción del bug: script CLI con la lógica exacta colgó (>400 iteraciones en rango de 6 días).
- Post-fix: `apoyosPorDia()` marca correctamente días 24–29 (29ms), rango largo de 15 días marca
  1–15 (3ms), rango jul→oct se clampa bien a los 30 días de septiembre (5ms). Apoyo de prueba creado
  dentro de transacción con rollback (cero residuo: users=50, apoyos=1, tipos=3 antes y después).
- Toggle Tabla↔Calendario x3 ciclos + mesAnterior/mesSiguiente/irAHoy vía `Livewire::test()`
  (ciclo de vida completo): grid e indicadores presentes, todo <100ms.

### 2026-08-20 — Apoyos S-4: migraciones, modelos y seeder

**Módulo nuevo:** Sistema de apoyos operativos (S-4) para gestión de apoyos de unidades militares.

**Migraciones (3):**
- `2026_08_20_000000_create_tipos_apoyo_table.php` — tabla `tipos_apoyo` (id, nombre unique, color hex)
- `2026_08_20_000001_create_apoyos_table.php` — tabla `apoyos` (13 FKs, estado, softDeletes)
- `2026_08_20_000002_create_apoyo_unidad_table.php` — pivot `apoyo_unidad` (apoyo_id + unidad_id, PK compuesta)

**Modelos (2):**
- `app/Models/TipoApoyo.php` — HasFactory + LogsActivity, `$table = 'tipos_apoyo'`, relación `apoyos()` hasMany
- `app/Models/Apoyo.php` — HasFactory + SoftDeletes + LogsActivity, 6 relaciones belongsTo (tipo, organismo, documentoNovedad, porDocumentoNovedad, cumplidoPor, registradoPor), relación `unidades()` belongsToMany via pivot

**Seeder:**
- `database/seeders/TipoApoyoSeeder.php` — 3 tipos iniciales: Vehículos (#28a745), Amplificación (#007bff), Antenistas (#dc3545)
- Registrado en `DatabaseSeeder.php`

**FKs verificadas:** `organismos` (tabla existente), `unidades` (tabla existente), `news` (tabla existente), `users` (tabla existente). Todas con restricciones apropiadas (restrict/nullOnDelete).

**Pendiente:** CRUD principal de Apoyos (fase siguiente — formulario con relaciones múltiples).

### 2026-08-20 — Apoyos S-4: CRUD de Tipos de Apoyo

**Componente Livewire:** `app/Livewire/TiposApoyo.php` — Nivel 2 (Intermedia) con ops-panel modal.

**Funcionalidad:**
- Listado con tabla (nombre, color swatch visual, cantidad de apoyos asociados)
- Crear/editar en modal ops-panel (x-teleport="body", CSS ops-panel.css)
- Formulario: nombre (unique) + color (input type="color", hex)
- Eliminación con modal de confirmación (bloqueo si tiene apoyos asociados)

**Archivos creados (4):**
- `app/Livewire/TiposApoyo.php` — Componente CRUD completo
- `app/Policies/TipoApoyoPolicy.php` — 5 métodos (viewAny/view/create/update/delete) con permisos `*_tipo_apoyo`
- `resources/views/livewire/apoyos/tipos/layout.blade.php` — Layout wrapper
- `resources/views/livewire/apoyos/tipos/index.blade.php` — Vista con ops-panel modal + tabla + paginación

**Archivos modificados (4):**
- `app/Providers/AppServiceProvider.php` — Import + Gate::policy + Gate::define('viewAny-tipos-apoyo')
- `database/seeders/PermisoSeeder.php` — 4 permisos: ver_tipos_apoyo, crear_tipo_apoyo, editar_tipo_apoyo, eliminar_tipo_apoyo
- `routes/web.php` — Ruta `GET /admin/apoyos/tipos` → `livewire.apoyos.tipos.layout`
- `config/adminlte.php` — Sidebar "Apoyos S-4" con submenu "Tipos de Apoyo" (fa-solid fa-tags)

**Pendiente:** CRUD principal de Apoyos (fase siguiente).

### 2026-08-20 — Apoyos S-4: CRUD principal de Apoyos

**Componente Livewire:** `app/Livewire/Apoyos.php` — Nivel 3 (Alta) con ops-panel modal, relaciones múltiples, combobox de búsqueda y lógica de estado.

**Funcionalidad:**
- Listado con tabla (Tipo swatch, Solicitante, Desde, Hasta, Unidades badges, Estado badge, Registrado por + initials)
- Filtros: TipoApoyo select, Estado select, búsqueda por texto (Solicitante/Documento)
- Paginación 15 registros
- Formulario crear/editar en ops-panel modal (x-teleport="body", full-screen)
- Combobox de búsqueda para Documento y Por Documento contra tabla `news` (wire:model.live.debounce.300ms)
  - Si se selecciona resultado: guarda `*_novedad_id`, limpia `*_texto`
  - Si no hay match: permite texto libre en `*_texto`, `*_novedad_id` queda null
- Select múltiple (checkbox-list) contra `unidades` (solo activos) — **ACTUALIZADO a multi-select con buscador (ms-* CSS classes)**
- Al cambiar estado a "cumplido": setea automáticamente `cumplido_por_id = auth()->id()` y `cumplido_at = now()`
- Al cambiar de "cumplido" a otro estado: limpia `cumplido_por_id` y `cumplido_at`
- `registrado_por_id = auth()->id()` automático al crear (no visible en form)
- Eliminación con modal de confirmación (soft delete)
- Iniciales en tabla de listado vía `User::initials()` (calculado, no almacenado en DB)

**Archivos creados (4):**
- `app/Livewire/Apoyos.php` — Componente CRUD completo (listado + crear/editar + eliminar)
- `app/Policies/ApoyoPolicy.php` — 5 métodos con permisos `*_apoyo`
- `resources/views/livewire/apoyos/layout.blade.php` — Layout wrapper
- `resources/views/livewire/apoyos/index.blade.php` — Vista con ops-panel modal + tabla + filtros + comboboxes + multi-select (ms-* classes)

**Archivos modificados (4):**
- `app/Providers/AppServiceProvider.php` — Import Apoyo + ApoyoPolicy + Gate::policy
- `database/seeders/PermisoSeeder.php` — 4 permisos: ver_apoyos, crear_apoyo, editar_apoyo, eliminar_apoyo
- `routes/web.php` — Ruta `GET /admin/apoyos` → `livewire.apoyos.layout`
- `config/adminlte.php` — Submenu "Apoyos" (fa-solid fa-hands-helping) arriba de "Tipos de Apoyo"

**Pendiente:** Click-en-día → reporte (Fase 4b).

### 2026-08-20 — Apoyos S-4: corrección modal + multi-select con buscador

**Problema 1 (Modal):** El modal "Nuevo Apoyo" se renderizaba pegado al lado izquierdo de la pantalla sin backdrop, porque `.ops-panel` tenía `style="max-width: 720px;"` inline. El `.ops-panel-overlay` usa `display: block` (no flex), así que un panel de 720px queda alineado a la izquierda. TiposApoyo funcionaba bien porque `.ops-panel` no tenía restricción de ancho.

**Solución 1:** Eliminar `style="max-width: 720px;"` del `.ops-panel`. El panel vuelve a ocupar 100% width/height (como define `ops-panel.css`), con `.ops-panel__content` centrado a 900px max-width automáticamente.

**Problema 2 (Multi-select):** El campo "A quien se dispuso" usaba un checkbox-list simple sin buscador, a diferencia del patrón multi-select con buscador (ms-* CSS classes) que ya existe en Guardias/Expedidos para "Unidades de destino".

**Solución 2:** Reemplazar el checkbox-list por el mismo patrón Alpine.js + CSS ms-* del componente `novedades-guardia.blade.php:215-375`, adaptado para usar IDs numéricos en vez de strings. Se agregaron 3 computed properties a `Apoyos.php` (`unidadesNombres`, `unidadesMap`, `unidadesOpciones`) para mapear entre IDs y nombres. El componente de Guardias NO fue modificado.

**Archivos modificados (2):**
- `resources/views/livewire/apoyos/index.blade.php` — Quitado `max-width: 720px` del `.ops-panel`, reemplazado checkbox-list por multi-select Alpine.js (ms-* pattern)
- `app/Livewire/Apoyos.php` — Agregados computed `unidadesNombres`, `unidadesMap`, `unidadesOpciones` para soporte del multi-select

**Patrón multi-select reusado de:** `resources/views/components/novedades-guardia/novedades-guardia.blade.php:215-375` (sin modificar)

**Pendiente:** Click-en-día → reporte (Fase 4b).

### 2026-08-20 — Apoyos S-4: toggle tabla/calendario + vista calendario visual

**Funcionalidad:** Vista de calendario alternativa a la tabla de listado de Apoyos, con navegación mensual y indicadores de color por tipo de apoyo.

**PASO 1 — Toggle:**
- Propiedad `$vistaActual` (string: `'tabla'` | `'calendario'`, default `'tabla'`)
- Botones pill `btn-group btn-group-sm` sobre el listado (FontAwesome 6 icons)
- Al estar en modo calendario: ocultar filtros de Tipo/Estado/búsqueda
- Eyebrow dinámico: muestra total de registros (tabla) o nombre del mes (calendario)

**PASO 2 — Vista de calendario:**
- Grilla de 7 columnas (Lun-Dom), header con gradiente ops (#0B2545) + borde #FFD200
- Navegación mes anterior/siguiente + botón "Hoy" (visible solo si no estamos en el mes actual)
- Cada celda muestra número de día + puntos de color por cada Tipo distinto presente ese día
- Usa `color` de `TipoApoyo` para los indicadores (ej: verde=Vehículos, rojo=Antenistas)
- Límite de 4 indicadores visibles por celda, luego "+N" para los restantes
- Celdas vacías para días sin apoyos
- Día actual resaltado con borde dorado (#FFD200)
- Leyenda de tipos activos debajo del calendario

**PASO 3 — Query eficiente:**
- Una sola query por mes: `Apoyo::with('tipo')->where('desde', '<=', finMes)->where('hasta', '>=', inicioMes)`
- Agrupación por día en PHP: itera el rango [desde→hasta] de cada apoyo y asigna a cada día del mes
- Un apoyo que dura varios días aparece marcado en TODOS esos días (no solo inicio)
- `#[Computed]` para cachear `apoyosPorDia` y `diasCalendario`

**Archivos modificados (3):**
- `app/Livewire/Apoyos.php` — Props `$vistaActual`/`$mes`/`$anio`, métodos `cambiarVista()`/`mesAnterior()`/`mesSiguiente()`/`irAHoy()`/`nombreMesActual()`, computed `apoyosPorDia()`, helpers `diasCalendario()`/`esHoy()`, render condicional
- `resources/views/livewire/apoyos/index.blade.php` — Toggle btn-group, sección calendario con grilla + indicadores + leyenda, tabla envuelta en `@if ($vistaActual === 'tabla')`
- `public/css/ops-panel.css` — Agregados estilos `.apoyos-calendar__*` (header, nav, weekdays, grid, day, dot, indicators, more, legend)

**Pendiente:** Click-en-día → reporte (Fase 4b).

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

### 2026-08-19 — Salidas de vehículos: fecha_sale como columna separada

**Problema:** La fecha de salida de un vehículo se infería de `guardia->date` en todas las vistas.
No había forma de registrar que un vehículo salió en fecha distinta a la de la guardia (ej: salida nocturna
que cruza medianoche).

**Solución:** Agregar columna `fecha_sale` (date, nullable) a `salidas_vehiculos`, con fallback
a `$salida->guardia->date` en todas las vistas. Patrón idéntico a BoletaCierre (`fecha_entra`
separado de `hora_entra`).

**Patrón de uso en vistas (siempre con fallback):**
```blade
{{-- Modelo tiene fecha_sale → usarla; sino → fecha de la guardia --}}
{{ $salida->fecha_sale?->format('d/m/Y') ?? $salida->guardia->date->format('d/m/Y') }}

{{-- En expresión con ->format() directo (sin nullsafe): --}}
{{ ($salida->fecha_sale ?? $salida->guardia->date)->format('d/m/Y') }}
```

**Componente Livewire (salidas-vehiculo.php):**
- Property: `public string $fecha_sale = '';`
- `abrirCrear()`: `$this->fecha_sale = $this->guardia->date->format('Y-m-d');` (default editable)
- `abrirEditar()`: `$this->fecha_sale = $salida->fecha_sale?->format('Y-m-d') ?? $salida->guardia->date->format('Y-m-d');`
- `$rules` en `guardar()`: `'fecha_sale' => 'required|date',`

**Migración:** `2026_08_19_000000_add_fecha_sale_to_salidas_vehiculos_table.php`
- `fecha_sale` date nullable after hora_sale
- Backfill: `UPDATE salidas_vehiculos SET fecha_sale = (SELECT guards.date FROM guards WHERE guards.id = salidas_vehiculos.guardia_id) WHERE fecha_sale IS NULL`

**Archivos modificados (8):**
- `database/migrations/2026_08_19_000000_add_fecha_sale_to_salidas_vehiculos_table.php` — nueva
- `app/Models/SalidaVehiculo.php` — `$fillable` + `$casts['fecha_sale' => 'date']`
- `resources/views/components/salidas-vehiculo/salidas-vehiculo.php` — property, abrirCrear, abrirEditar, rules
- `resources/views/components/salidas-vehiculo/salidas-vehiculo.blade.php` — input fecha (grid 4x col-md-3), tabla, boleta modal
- `resources/views/livewire/salidas-pendientes.blade.php` — 2 referencias con fallback
- `resources/views/admin/guardias/pdf/novedades.blade.php` — 2 referencias con fallback
- `resources/views/livewire/vehiculos/index.blade.php` — 1 referencia con fallback
- `resources/views/livewire/conductores/index.blade.php` — 1 referencia con fallback

### 2026-08-19 — Correos fallidos: fix $messageId null + tests unitarios

**Problema:** `registrarFallo()` en `EnviarNovedadGuardiaMail::handle()` intentaba acceder a `$messageId`
en el `catch`, pero si `Mail::send()` lanzaba una excepción antes de la línea donde se asigna
`$messageId = $mailable->messageId`, la variable no existía → PHP Warning que propagaba el error
al `LoteJob` (EnviarNovedadesGuardiaLoteJob).

**Solución:** Inicializar `$messageId = null` antes del bloque `try`, y pasar `$messageId` al `catch`
como `?string`. Así el registro en `guardia_correos_fallidos` queda con `message_id = null`
en vez de propagar el error.

**Migración:** `2026_08_19_000000_add_tipo_message_id_to_guardia_correos_fallidos_table.php`
- Columna `tipo` (string, 'inmediato' | 'lote', default 'inmediato') en `guardia_correos_fallidos`

**Archivos modificados (4):**
- `app/Jobs/EnviarNovedadGuardiaMail.php` — `$messageId = null` antes del try, `$messageId` como `?string` en catch
- `app/Jobs/EnviarNovedadesGuardiaLoteJob.php` — `DB::table('guardia_correos_fallidos')->insert()` ahora pasa `tipo => 'lote'`
- `resources/views/livewire/correos-fallidos/correos-fallidos.blade.php` — Polling temporal (x-if + wire:poll + $watch) para corregir race condition post-afterResponse

**Tests unitarios (14 tests, 14 passing):**
- `tests/Feature/Jobs/EnviarNovedadGuardiaMailTest.php`
- `registrarFallo` con `message_id = null` → persiste registro válido
- `registrarFallo` con adjuntos/zip → persiste `con_adjuntos`/`con_zip`
- `registrarFallo` con `message_id` no null → persiste el ID
- `clasificarMotivo` → 8 escenarios (SMTP connection, timeout, auth, mailbox full, invalid address, unknown, quota exceeded, unauthenticated)

**Estado de la suite:** 314 tests · 293 passed · 3 failed · 16 errors (todos pre-existentes, 0 regresiones)

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
