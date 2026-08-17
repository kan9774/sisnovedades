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

## Qué NO migrar / no tocar

- `web/index.blade.php` — Landing estática
- `auth/*` — Login (página única, no necesita reactividad)
- `admin.guardias.pdf.*` — Generación de PDF (renderizado servidor-side)
- `layouts/*`, `partials/*`, `emails/*` — Templates compartidos
- `admin.guardias.pdf-preview` — Ruta pública de preview HTML
- Controladores ya migrados: `OficinaController`, `OrganismoController`, `TipoVehiculoController`, `EstadoPalomaController`, `PermisoController`, `RolController`, `ActivityLogController`, `ConductorController`, `PalomarController`, `PalomaController`, `VueloController` — **eliminar tras migración**
- `VehiculoController` — desactivado (rutas reemplazadas por Livewire), pendiente de borrado manual

---

## Reglas de autorización relevantes

- `Gate::before()` global: `isSuperAdmin() ? true : null` — exime a SuperAdmin de TODOS los gates
- `isAdmin()` solo en GuardiaPolicy (7 métodos) y UserPolicy (update/delete) por decisión explícita
- Resto de Policies: 100% basadas en `HasPermisos()`, sin atajos de rol
- Roles operativos (capitán/oficial/escribiente) acceden por lógica de rol EN la Policy, no por gate
- `seeded_permissions_locked` en tabla `rols`: si es `true`, el RolSeeder no reasigna permisos automáticamente
- **Riesgo:** correr `RolSeeder` después de asignar permisos manualmente **borra las asignaciones manuales** (`sync()` reemplaza todo)
- Nuevo permiso: agregar en `PermisoSeeder` → correr `php artisan db:seed --class=PermisoSeeder` → asignar en panel de Roles → agregar chequeo en Policy o `Gate::define()` en AppServiceProvider
