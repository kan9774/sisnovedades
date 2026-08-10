# Candidatas a Migración de Blade → Livewire

> Archivo generado con codebase-memory-mcp — Análisis del grafo de conocimiento del proyecto `novedades`.

## Contexto

El proyecto usa Laravel 11 + Livewire 3 + Alpine.js. Ya existen **~30+ componentes Livewire** implementados con patrones consistentes. Este archivo identifica las vistas Blade que aún dependen de controladores tradicionales y pueden migrarse.

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
| 1 | `admin.oficinas.index` | `OficinaController` | `Oficina` | index, create, edit | ⭐ Simple | CRUD con `withCount('users')`, validación de usuarios asociados antes de eliminar |
| 2 | `admin.organismos.index` | `OrganismoController` | `Organismo` | index, create, edit | ⭐ Simple | CRUD mínimo, solo campo `name`. Validación de novedades asociadas |
| 3 | `admin.vehiculos.tipos.index` | `TipoVehiculoController` | `TipoVehiculo` | index, create, edit | ⭐ Simple | CRUD con campo `nombre`. Validación de vehículos asociados |
| 4 | `admin.palomar.estados.index` | `EstadoPalomaController` | `EstadoPaloma` | index, create, edit | ⭐ Simple | CRUD mínimo para catálogo de estados |

**Patrón a seguir:** `CategoriasDocumentos` (CRUD inline con edición fila por fila)

---

### Nivel 2 — Intermedia (CRUD + filtros + catálogos dependientes)

Requieren selectores que cargan opciones de otros catálogos, o formularios con más campos.

| # | Vista Blade | Controlador | Modelo | Vistas | Complejidad | Notas |
|---|-------------|-------------|--------|--------|-------------|-------|
| 5 | `admin.permisos.index` | `PermisoController` | `Permission` | index, create, edit | ⭐⭐ Media | CRUD con `withCount('rols')`. Create/Edit necesita catálogo de `modulos` |
| 6 | `admin.roles.index` | `RolController` | `Rol` | index, create, edit | ⭐⭐ Media | CRUD con `withCount('users')`. Create/Edit necesita checkboxes de permisos agrupados por módulo (`agruparPermisosPorModulo`) |
| 7 | `admin.notificaciones.index` | `NotificationController` | `DatabaseNotification` | index | ⭐⭐ Media | Filtro (todas/no_leidas), paginación, acciones "marcar como leída" y "marcar todas" |
| 8 | `admin.logs.index` | `ActivityLogController` | `Activity` (spatie) | index | ⭐⭐ Media | Filtros múltiples (log_name, event, user_id, fecha). Paginación con query string |

**Patrón a seguir:** `Documentos` (filtros reactivos + modal de formulario)

---

### Nivel 3 — Alta (formularios complejos + relaciones múltiples)

Formularios con muchos campos, validaciones custom, múltiples catálogos dependientes.

| # | Vista Blade | Controlador | Modelo | Vistas | Complejidad | Notas |
|---|-------------|-------------|--------|--------|-------------|-------|
| 9 | `admin.conductores.*` | `ConductorController` | `Conductor` | index, create, show, edit | ⭐⭐⭐ Alta | 15+ campos, fechas de vencimiento, validaciones de licencia. Controller tiene `show` con historial de salidas |
| 10 | `admin.vehiculos.*` | `VehiculoController` | `Vehiculo` | index, create, show, edit | ⭐⭐⭐ Alta | 15+ campos, 4 catálogos dependientes (tipo_vehiculo, combustible, lubricante, rodado), upload de acta, export Excel |
| 11 | `admin.palomar.palomares.*` | `PalomarController` | `Palomar` | index, create, show, edit | ⭐⭐⭐ Alta | CRUD con `withCount('palomas')`, validación de palomas asociadas, reporte PDF |
| 12 | `admin.palomar.palomas.*` | `PalomaController` | `Paloma` | index, create, show, edit | ⭐⭐⭐ Alta | Formulario complejo: padre/madre con validación de sexo, historial de estados, relaciones múltiples |
| 13 | `admin.palomar.vuelos.*` | `VueloController` | `Vuelo` | index, create, edit, resultados | ⭐⭐⭐ Alta | CRUD + resultados. Lógica de estados de palomas, cálculo tiempo/velocidad, pivot con historial |

**Patrón a seguir:** `PdfDestinatarios` (CRUD completo con asignación de usuarios, `WithPagination`)

---

### Nivel 4 — Muy alta (lógica de negocio compleja)

| # | Vista Blade | Controlador | Modelo | Vistas | Complejidad | Notas |
|---|-------------|-------------|--------|--------|-------------|-------|
| 14 | `admin.users.index` | `UserController` | `User` | index, userdelete | ⭐⭐⭐⭐ Muy alta | Búsqueda multi-campo, paginación con `leftJoin` para orden jerárquico, usuarios incompletos, soft delete, force delete, destroyIncompleto con transacción |
| 15 | `admin.guardias.*` | `GuardiaController` | `Guard` | index, create, show, edit, trashed | ⭐⭐⭐⭐ Muy alta | 242 líneas. Estados: crear, cerrar, reactivar, restaurar, force-delete. Dependencias: capitanes, oficiales, escribientes, tipos_vehiculo. Show con carga masiva de relaciones |

---

## Resumen estadístico

| Categoría | Cantidad | Vistas Blade |
|-----------|----------|--------------|
| Nivel 1 — Simple | 4 | ~12 archivos |
| Nivel 2 — Intermedia | 4 | ~10 archivos |
| Nivel 3 — Alta | 5 | ~25 archivos |
| Nivel 4 — Muy alta | 2 | ~15 archivos |
| **Total** | **15** | **~62 archivos** |

## Controladores que pueden eliminarse tras migración

| Controlador | Rutas a migrar |
|-------------|----------------|
| `OficinaController` | 5 rutas (CRUD completo) |
| `OrganismoController` | 5 rutas (CRUD completo) |
| `TipoVehiculoController` | 5 rutas (CRUD completo) |
| `EstadoPalomaController` | 3 rutas (index, create, edit) |
| `PermisoController` | 5 rutas (CRUD completo) |
| `RolController` | 5 rutas (CRUD + agrupación permisos) |
| `NotificationController` | 4 rutas (index + acciones) |
| `ActivityLogController` | 1 ruta (index con filtros) |
| `ConductorController` | 5 rutas (CRUD completo) |
| `VehiculoController` | 7 rutas (CRUD + export) |
| `PalomarController` | 6 rutas (CRUD + reporte) |
| `PalomaController` | 6 rutas (CRUD + historial) |
| `VueloController` | 7 rutas (CRUD + resultados) |
| `UserController` | 6 rutas (CRUD + incompletos) |
| `GuardiaController` | 11 rutas (CRUD + estados) |

---

## Recomendación de orden de migración

1. **Sprint 1:** Nivel 1 (Oficinas, Organismos, TipoVehiculo, EstadoPaloma) — ganar confianza
2. **Sprint 2:** Nivel 2 (Permisos, Roles, Notificaciones, Logs) — patrones de filtros
3. **Sprint 3:** Nivel 3 (Conductores, Vehiculos, Palomar, Palomas, Vuelos) — formularios complejos
4. **Sprint 4:** Nivel 4 (Users, Guardias) — lógica de negocio crítica

---

## No migrar (no son candidatas)

- `web/index.blade.php` — Landing page estática
- `auth/*` — Formulario de login (página única, no necesita reactividad)
- `admin.guardias.pdf.*` — Generación de PDF (renderizado servidor-side)
- `layouts/*`, `partials/*`, `emails/*` — Templates compartidos
- `admin.guardias.pdf-preview` — Ruta pública de preview HTML
