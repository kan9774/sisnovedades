# Candidatas a Migración de Blade → Livewire

> Archivo generado con codebase-memory-mcp — Análisis del grafo de conocimiento del proyecto `novedades`.
> Última actualización: 2026-08-15

## Contexto

El proyecto usa Laravel 13 + Livewire 4 + Alpine.js. Existen **68 componentes Livewire** implementados con patrones consistentes. Este archivo identifica las vistas Blade que aún dependen de controladores tradicionales y pueden migrarse.

## Componentes Landing

| # | Componente | Nivel | Estado |
|---|------------|-------|--------|
| 1 | `Landing\Contacto` | 1 — Simple | ✅ Activo |
| 2 | `Landing\ContactoSeccion` | 1 — Simple | ✅ Activo |
| 3 | `Landing\Hero` | 1 — Simple | ✅ Activo |
| 4 | `Landing\Navbar` | 1 — Simple | ✅ Activo |
| 5 | `Landing\Footer` | 1 — Simple | ✅ Activo |
| 6 | `Landing\Nosotros` | 1 — Simple | ✅ Activo |
| 7 | `Landing\Servicios` | 1 — Simple | ✅ Activo |
| 8 | `Landing\Documentos` | 2 — Intermedia | ✅ Activo |
| 9 | `Landing\NovedadesCerradas` | 2 — Intermedia | ✅ Activo |
| 10 | `Landing\Crucigrama` | 1 — Simple | ✅ Activo |
| 11 | `Landing\SudokuGame` | 2 — Intermedia | ✅ Activo |
| 12 | `Landing\SopaLetras` | 2 — Intermedia | ✅ Activo |
| 13 | `Landing\TetrisGame` | 2 — Intermedia | ✅ Activo (2026-08-12) |
| 14 | `Landing\Recreacion` | 1 — Simple | ✅ Activo |

---

## Migraciones completadas

| # | Componente | Nivel | Estado | Rutas web | Controlador eliminado |
|---|------------|-------|--------|-----------|----------------------|
| 1 | `Livewire\Oficinas` | 1 — Simple | ✅ MIGRADO | `Route::get('/oficinas', ...)` → `livewire.oficinas.layout` | `OficinaController.php` ✅ |
| 2 | `Livewire\Permisos` | 2 — Intermedia | ✅ MIGRADO | `Route::get('/permisos', ...)` → `livewire.permisos.layout` | `PermisoController.php` ✅ |
| 3 | `Livewire\Roles` | 2 — Intermedia | ✅ MIGRADO | `Route::get('/roles', ...)` → `livewire.roles.layout` | `RolController.php` ✅ |
| 4 | `Livewire\Notificaciones` | 2 — Intermedia | ✅ MIGRADO | `Route::get('/notificaciones', ...)` → `livewire.notificaciones.layout` | `NotificationController::index/markAsRead/markAllAsRead` ✅ |
| 5 | `Livewire\Logs` | 2 — Intermedia | ✅ MIGRADO | `Route::get('/logs', ...)` → `livewire.logs.layout` | `ActivityLogController` ✅ |
| 6 | `Livewire\Organismos` | 1 — Simple | ✅ MIGRADO | `Route::get('/organismos', ...)` → `livewire.organismos.layout` | `OrganismoController` ✅ |
| 7 | `Livewire\TiposVehiculo` | 1 — Simple | ✅ MIGRADO | `Route::get('/vehiculos/tipos', ...)` → `livewire.vehiculos.tipos.layout` | `TipoVehiculoController` ✅ |
| 8 | `Livewire\EstadosPaloma` | 1 — Simple | ✅ MIGRADO | `Route::get('/palomar/estados-paloma', ...)` → `livewire.palomar.estados.layout` | `EstadoPalomaController` ✅ |
| 9 | `Livewire\Conductores` | 3 — Alta | ✅ MIGRADO | `Route::get('/conductores', ...)` → `livewire.conductores.layout` | `ConductorController` ✅ |
| 10 | `Livewire\Vehiculos` | 3 — Alta | ✅ MIGRADO | `Route::get('/vehiculos', ...)` → `livewire.vehiculos.layout` | `VehiculoController` (desactivado, no eliminado) |
| 11 | `Livewire\Palomas` | 3 — Alta | ✅ MIGRADO | `Route::get('/palomar/palomas', ...)` → `livewire.palomas-layout` | `PalomaController` (eliminada del filesystem) |

**Patrón Nivel 1:** `#[Computed]` + `WithPagination` + `UsesBootstrapPagination` + `mount()` con `$this->authorize('viewAny')` + `updatedSearch()` con `$this->resetPage()` + formulario inline con `wire:confirm` para eliminar.
**Patrón Nivel 2:** `#[Computed]` + sin paginación (->get()) + `mount()` con `$this->authorize('viewAny')` + `updatedSearch()` + modal de formulario con `x-ops-card` + `confirmDelete()` / `executeDelete()` + checkboxes de permisos agrupados por módulo (`agruparPermisosPorModulo` + `formatearNombreModulo`).

---

## Patrón Livewire adoptado por el proyecto

Los componentes existentes siguen esta estructura:

```php
class NombreComponent extends Component
{
    // Propiedades públicas = estado del formulario/UI
    public $search = '';
    public $showForm = false;
    public $formTipo = 'create';
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // Datos con #[Computed] para caching
    #[Computed]
    public function registros()
    {
        return Modelo::withCount('relaciones')
            ->when($this->search, fn($q) => $q->where('campo', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);
    }

    // Autorización inline
    public function crear()
    {
        $this->authorize('create', Modelo::class);
        $this->showForm = true;
    }

    // Guardar con try/finally
    public function guardar()
    {
        $this->authorize('create', Modelo::class);
        $this->validate([...]);
        $this->loading = true;
        try {
            Modelo::create($this->only([...]));
            $this->successMsg = '...';
            $this->reset([...]);
        } catch (\Exception $e) {
            $this->errorMsg = 'Error: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.nombre-componente.index', [
            'registros' => $this->registros(),
        ]);
    }
}
```

**Convenciones clave:**
- Estado UI en propiedades públicas (`showForm`, `showTrash`, `showPreview`)
- `#[Computed]` para consultas con filtros reactivos
- `wire:loading` para feedback visual
- `wire:confirm` para eliminar (sin modales JS externos)
- `dispatch()` para eventos entre componentes
- `WithFileUploads` para archivos
- Validación reactiva con `updated($propertyName)` y `validateOnly()`
- Mensajes en `$successMsg` / `$errorMsg` (no session flash)

---

## Candidatas por prioridad

### Nivel 1 — Simple (CRUD básico, sin filtros complejos)

Estas son las más fáciles de migrar: una tabla con paginación, formulario de alta/edición inline o modal, y eliminar con confirmación.

| # | Vista Blade | Controlador | Modelo | Vistas | Complejidad | Notas |
|---|-------------|-------------|--------|--------|-------------|-------|
| ~~1~~ | ~~`admin.oficinas.index`~~ | ~~`OficinaController`~~ | ~~`Oficina`~~ | ~~index, create, edit~~ | ~~⭐ Simple~~ | ~~MIGRADO~~ |
| 2 | `admin.organismos.index` | `OrganismoController` | `Organismo` | index, create, edit | ⭐ Simple | ✅ MIGRADO |
| 3 | `admin.vehiculos.tipos.index` | `TipoVehiculoController` | `TipoVehiculo` | index, create, edit | ⭐ Simple | ✅ MIGRADO |
| 4 | `admin.palomar.estados.index` | `EstadoPalomaController` | `EstadoPaloma` | index, create, edit | ⭐ Simple | ✅ MIGRADO |

**Patrón a seguir:** `CategoriasDocumentos` (CRUD inline con edición fila por fila)

---

### Nivel 2 — Intermedia (CRUD + filtros + catálogos dependientes)

Requieren selectores que cargan opciones de otros catálogos, o formularios con más campos.

| # | Vista Blade | Controlador | Modelo | Vistas | Complejidad | Notas |
|---|-------------|-------------|--------|--------|-------------|-------|
| ~~5~~ | ~~`admin.permisos.index`~~ | ~~`PermisoController`~~ | ~~`Permission`~~ | ~~index, create, edit~~ | ~~⭐⭐ Media~~ | ~~MIGRADO~~ |
| ~~6~~ | ~~`admin.roles.index`~~ | ~~`RolController`~~ | ~~`Rol`~~ | ~~index, create, edit~~ | ~~⭐⭐ Media~~ | ~~MIGRADO~~ |
| ~~7~~ | ~~`admin.notificaciones.index`~~ | ~~`NotificationController`~~ | ~~`DatabaseNotification`~~ | ~~index~~ | ~~⭐⭐ Media~~ | ~~MIGRADO~~ |
| 8 | `admin.logs.index` | `ActivityLogController` | `Activity` (spatie) | index | ⭐⭐ Media | ✅ MIGRADO |

**Patrón a seguir:** `Documentos` (filtros reactivos + modal de formulario)

---

### Nivel 3 — Alta (formularios complejos + relaciones múltiples)

Formularios con muchos campos, validaciones custom, múltiples catálogos dependientes.

| # | Vista Blade | Controlador | Modelo | Vistas | Complejidad | Notas |
|---|-------------|-------------|--------|--------|-------------|-------|
| 9 | `admin.conductores.*` | `ConductorController` | `Conductor` | index, create, show, edit | ⭐⭐⭐ Alta | ✅ MIGRADO |
| ~~10~~ | ~~`admin.vehiculos.*`~~ | ~~`VehiculoController`~~ | ~~`Vehiculo`~~ | ~~index, create, show, edit~~ | ~~⭐⭐⭐ Alta~~ | ~~MIGRADO~~ |
| ~~11~~ | ~~`admin.palomar.palomares.*`~~ | ~~`PalomarController`~~ | ~~`Palomar`~~ | ~~index, create, show, edit~~ | ~~⭐⭐⭐ Alta~~ | ~~MIGRADO~~ |
| ~~12~~ | ~~`admin.palomar.palomas.*`~~ | ~~`PalomaController`~~ | ~~`Paloma`~~ | ~~index, create, show, edit~~ | ~~⭐⭐⭐ Alta~~ | ~~MIGRADO — PalomaController eliminado, Livewire\Palomas (389 líneas) + Livewire\Palomas\PalomaShow (33 líneas)~~ |
| 13 | `admin.palomar.vuelos.*` | `VueloController` | `Vuelo` | index, create, edit, resultados | ⭐⭐⭐ Alta | ✅ MIGRADO — `Livewire\Vuelos` (476 líneas) + `Livewire\Vuelos\VuelosResultados` (151 líneas). CRUD + resultados con cálculo tiempo/velocidad. Controller y vistas Blade eliminadas. |

**Patrón a seguir:** `PdfDestinatarios` (CRUD completo con asignación de usuarios, `WithPagination`)

---

### Nivel 4 — Muy alta (lógica de negocio compleja)

| # | Vista Blade | Controlador | Modelo | Vistas | Complejidad | Notas |
|---|-------------|-------------|--------|--------|-------------|-------|
| 14 | `admin.users.index` | `UserController` | `User` | index, userdelete | ⭐⭐⭐⭐ Muy alta | Búsqueda multi-campo, paginación con `leftJoin` para orden jerárquico, usuarios incompletos, soft delete, force delete, destroyIncompleto con transacción |
| ~~15~~ | ~~`admin.guardias.*`~~ | ~~`GuardiaController`~~ | ~~`Guard`~~ | ~~index, create, show, edit, trashed~~ | ~~⭐⭐⭐⭐ Muy alta~~ | ~~✅ MIGRADO (Fase 4: cerrar/reactivar desde show.blade.php → `GuardiaAcciones` component. CRUD, papelera, cerrar/reactivar/pdf desde tabla migrados en `Guardias` component. Controller reducido a show()/Hoy()/pdf()~~ |

---

## Resumen estadístico

| Categoría | Cantidad | Vistas Blade |
|-----------|----------|--------------|
| Landing (nuevos) | 14 | ~14 archivos |
| Nivel 1 — Simple | 4 (4 migrados) | ~12 archivos |
| Nivel 2 — Intermedia | 3 (3 migrados) | ~7 archivos |
| Nivel 3 — Alta | 5 (4 migrados) | ~25 archivos |
| Nivel 4 — Muy alta | 1 (1 migrado) | ~5 archivos |
| **Total Livewire** | **69** | |
| **Total migraciones** | **17** (14 migrados) | **~48 archivos** |

## Controladores que pueden eliminarse tras migración

| Controlador | Rutas a migrar | Estado |
|-------------|----------------|--------|
| ~~`OficinaController`~~ | ~~5 rutas (CRUD completo)~~ | ~~✅ ELIMINADO~~ |
| ~~`OrganismoController`~~ | ~~5 rutas (CRUD completo)~~ | ~~✅ ELIMINADO~~ |
| ~~`TipoVehiculoController`~~ | ~~5 rutas (CRUD completo)~~ | ~~✅ ELIMINADO~~ |
| ~~`EstadoPalomaController`~~ | ~~3 rutas (index, create, edit)~~ | ~~✅ ELIMINADO~~ |
| ~~`PermisoController`~~ | ~~5 rutas (CRUD completo)~~ | ~~✅ ELIMINADO~~ |
| ~~`RolController`~~ | ~~5 rutas (CRUD + agrupación permisos)~~ | ~~✅ ELIMINADO~~ |
| ~~`NotificationController`~~ | ~~index/markAsRead/markAllAsRead~~ | ~~✅ MIGRADO~~ |
| `NotificationController` | `tomar()` (sigue en uso en web.php:260) | ⚠️ Parcial — solo index/markAsRead/markAllAsRead migrados |
| `ActivityLogController` | 1 ruta (index con filtros) | ✅ ELIMINADO |
| ~~`ConductorController`~~ | ~~5 rutas (CRUD completo)~~ | ~~✅ ELIMINADO~~ |
| `VehiculoController` | 7 rutas (CRUD + export) | ⏳ Desactivado (rutas reemplazadas por Livewire, archivo conservado) |
| ~~`PalomarController`~~ | ~~6 rutas (CRUD + reporte)~~ | ~~✅ ELIMINADO~~ |
| ~~`PalomaController`~~ | ~~6 rutas (CRUD + historial)~~ | ~~✅ ELIMINADO~~ |
| ~~`VueloController`~~ | ~~7 rutas (CRUD + resultados)~~ | ~~✅ ELIMINADO~~ |
| `UserController` | 6 rutas (CRUD + incompletos) | ⏳ Pendiente |
| ~~`GuardiaController`~~ | ~~8 rutas (CRUD + cerrar + reactivar)~~ | ~~✅ MIGRADO (reducido a show()/Hoy()/pdf())~~ |

---

## Recomendación de orden de migración

1. **Sprint 1:** Nivel 1 (Oficinas, Organismos, TipoVehiculo, EstadoPaloma) — ganar confianza ✅ **COMPLETADO**
2. **Sprint 2:** Nivel 2 (Permisos, Roles, Notificaciones, Logs) — patrones de filtros ✅ **COMPLETADO**
3. **Sprint 3:** Nivel 3 restante (Vehiculos ✅ migrado, Palomar ✅ migrado, Palomas ✅ migrado, Vuelos ✅ migrado) — formularios complejos
4. **Sprint 4:** Nivel 4 (Users) — lógica de negocio crítica ✅ Guardias **COMPLETADO**
5. **Landing:** Componentes interactivos (Tetris ✅, Crucigrama, Sudoku, SopaLetras) ✅ **COMPLETADO**

---

## No migrar (no son candidatas)

- `web/index.blade.php` — Landing page estática
- `auth/*` — Formulario de login (página única, no necesita reactividad)
- `admin.guardias.pdf.*` — Generación de PDF (renderizado servidor-side)
- `layouts/*`, `partials/*`, `emails/*` — Templates compartidos
- `admin.guardias.pdf-preview` — Ruta pública de preview HTML
- `livewire/landing/*` — Ya son componentes Livewire (Tetris, Crucigrama, Sudoku, SopaLetras, etc.)

---

> **Nota (2026-08-11):** `VehiculoController.php` y las vistas `admin/vehiculos/*.blade.php` (excepto `mantenimientos/`) se conservan en el filesystem sin uso tras la migración a Livewire. Quedan pendientes de borrado manual una vez confirmado en navegador que el módulo `livewire.vehiculos.*` funciona correctamente.

> **Nota (2026-08-12):** Guardias migrado en 4 fases. Fase 4 (esta): `GuardiaAcciones` component reemplaza forms POST a cerrar/reactivar en show.blade.php. `GuardiaController` reducido a 3 métodos (show/Hoy/pdf). Vistas admin/guardias/{create,edit,index}.blade.php eliminadas. Rutas admin.guardias.cerrar/reactivar eliminadas.

> **Nota (2026-08-13):** Palomar migrado. `PalomarController` (120 líneas) reemplazado por `Livewire\Palomares`. Se agregó `PalomarPdfGenerator` como support class con métodos estáticos `generar()` y `nombreArchivo()`, invocado desde ruta clásica `<a href>` (no `wire:click`). Vistas admin/palomar/*.blade.php eliminadas.

> **Nota (2026-08-15):** Palomas migrado. `PalomaController` (181 líneas) reemplazado por `Livewire\Palomas` (389 líneas) + `Livewire\Palomas\PalomaShow` (33 líneas). CRUD completo con modal ops-panel, validación de sexo padre/madre, historial de estados con registro automático. Controlador eliminado del filesystem. Vistas admin/palomar/palomas/*.blade.php eliminadas.

> **Nota (2026-08-16):** Vuelos migrado. `VueloController` (322 líneas) reemplazado por `Livewire\Vuelos` (476 líneas) + `Livewire\Vuelos\VuelosResultados` (151 líneas). CRUD completo con selección de palomas, cálculo de tiempo/velocidad, historial de estados. Controlador y vistas Blade `admin/palomar/vuelos/*.blade.php` eliminadas.
