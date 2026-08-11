# Contexto del Proyecto — novedades (Laravel 13 + Livewire 3)

## Stack
- Laravel 11 + Livewire 3 + Alpine.js + Bootstrap 4.6.1
- ~30+ componentes Livewire ya implementados
- Patrón CRUD: tabla + modal ops-panel (x-teleport="body")
- CSS compartido: `public/css/ops-panel.css`
- Traits: `App\Traits\UsesBootstrapPagination` (paginationTheme='bootstrap')
- Gate custom: `Gate::define('viewAny-log', ...)` en AppServiceProvider

## Estructura de migraciones
`CANDIDATAS_LIVEWIRE.md` — tracker con 4 niveles de complejidad

## Migraciones completadas ✅
| # | Componente | Nivel | Rutas | Controlador |
|---|-----------|-------|-------|-------------|
| 1 | `Livewire\Oficinas` | 1 — Simple | GET `/oficinas` → `livewire.oficinas.layout` | OficinaController eliminado |
| 2 | `Livewire\Permisos` | 2 — Intermedia | GET `/permisos` → `livewire.permisos.layout` | PermisoController eliminado |
| 3 | `Livewire\Roles` | 2 — Intermedia | GET `/roles` → `livewire.roles.layout` | RolController eliminado |
| 4 | `Livewire\Notificaciones` | 2 — Intermedia | GET `/notificaciones` → `livewire.notificaciones.layout` | NotificationController::index/markAsRead/markAllAsRead eliminados |
| 5 | `Livewire\Logs` | 2 — Intermedia | GET `/logs` → `livewire.logs.layout` | ActivityLogController eliminado |
| 6 | `Livewire\Organismos` | 1 — Simple | GET `/organismos` → `livewire.organismos.layout` | OrganismoController eliminado |
| 7 | `Livewire\TiposVehiculo` | 1 — Simple | GET `/vehiculos/tipos` → `livewire.vehiculos.tipos.layout` | TipoVehiculoController eliminado |
| 8 | `Livewire\EstadosPaloma` | 1 — Simple | GET `/palomar/estados-paloma` → `livewire.palomar.estados.layout` | EstadoPalomaController eliminado |

## Patrón CRUD inline (Nivel 1 — campos simples)
- `#[Computed]` + `WithPagination` + `UsesBootstrapPagination`
- `mount()` con `$this->authorize('viewAny', Modelo::class)`
- `updatedSearch()` con `resetPage()`
- Formulario inline (sin modal x-teleport), campos directos en la tabla
- `withCount('relacion')` para mostrar contadores en tabla
- `eliminar($id)` con bloqueo por `relacion_count > 0`
- Validación unique con tabla explícita: `unique:tabla,campo`
- Policy registrada en AppServiceProvider con `Gate::policy(Modelo::class, ModeloPolicy::class)`

## Patrón CRUD inline con checkbox 'activo' (Nivel 1 — campos simples)
- Mismo patrón base + campo `activo` (boolean) con `wire:model` directo
- Validación: `max:50` (TipoVehiculo), `max:100` (EstadoPaloma), `max:255` (Organismo)
- `formActivo` como booleano, `formColor` nullable max:50 (texto libre)
- Tabla muestra badge activo/inactivo según estado

## Patrón Livewire adoptado
```php
class Componente extends Component
{
    use WithPagination, UsesBootstrapPagination;

    // Propiedades = estado UI (showForm, search, etc.)
    public $search = '';
    public $showForm = false;
    public $formTipo = 'create';
    public $successMsg = '';
    public $errorMsg = '';

    // Datos con #[Computed] para caching
    #[Computed]
    public function registros() { ... }

    // mount() con autorización
    public function mount() { $this->authorize('viewAny', Modelo::class); }

    // updated*() con resetPage() para filtros
    public function updatedSearch() { $this->resetPage(); }

    // Guardar con try/finally, eliminar con wire:confirm
    public function guardar() { ... }
    public function eliminar() { ... }

    // render() pasando #[Computed] a la vista
    public function render() {
        return view('livewire.componente.index', ['registros' => $this->registros()]);
    }
}
```

## Patrón Nivel 1 — CRUD simple
`#[Computed]` + `WithPagination` + `mount()` con `$this->authorize('viewAny')` + `updatedSearch()` + formulario inline con `wire:confirm` para eliminar

## Patrón Nivel 2 — CRUD + filtros + modal
`#[Computed]` + paginación + `mount()` + `updated*()` + modal con `x-teleport="body"` + `ops-panel-overlay` + `:class="{ 'is-open': $wire.showForm }"` + dispatch events (`abrir-modal-*`/`cerrar-modal-*`)

## Patrón ops-panel (modal)
- `<template x-teleport="body">` envuelve al div (NO atributo en el div)
- `:class="{ 'is-open': $wire.showForm }"` para visibilidad (NO `x-show`)
- `$watch('$wire.showForm', ...)` con `$wire.` explícito
- `wire:ignore.self` REMOVIDO
- CSS en `public/css/ops-panel.css` con `.ops-panel-overlay.is-open`

## Patrón Logs — Filtros múltiples (solo lectura)
- `#[Url(as: 'log_name')]` para reflejar filtros en query string (compartible)
- `#[Url]` para los demás (nombre de variable)
- `updated*()` con `resetPage()` para cada filtro
- `#[Computed]` separado para cada lista de filtros (logNames, eventos)
- Bootstrap collapse por fila para ver propiedades JSON
- Botón "Limpiar filtros" que resetea todas las propiedades

## Modelos clave
- `Rol` → tabla `rols`, fillable `['name', 'description']`, relaciones `permisos()` (BelongsToMany), `users()` (BelongsToMany con pivote `role_user`)
- `RolPolicy` → todos los métodos devuelven `$user->isAdmin()`
- `Permission` → estándar Spatie
- `User` → `HasPermisos()`, `isAdmin()`
- `Activity` (spatie/laravel-activitylog) → `causer` relation, `properties` (JSON)

## Bloqueos de eliminación
- Rol `admin` → `name === 'admin'` no se puede eliminar
- Rol con `users()->count() > 0` → no se puede eliminar

## No migrar
- `web/index.blade.php` — landing estática
- `auth/*` — login (página única)
- `admin.guardias.pdf.*` — generación PDF
- `layouts/*`, `partials/*`, `emails/*` — templates compartidos

## Pendientes
| # | Componente | Nivel | Notas |
|---|-----------|-------|-------|
| 9 | Conductores | 3 — Alta | 15+ campos, vencimientos |
| 10 | Vehiculos | 3 — Alta | 15+ campos, 4 catálogos, upload acta, export Excel |
| 11 | Palomar | 3 — Alta | CRUD + withCount + PDF |
| 12 | Palomas | 3 — Alta | Padre/madre, historial estados |
| 13 | Vuelos | 3 — Alta | CRUD + resultados + cálculo velocidad |
| 14 | Users | 4 — Muy alta | Búsqueda multi-campo, leftJoin, soft delete |
| 15 | Guardias | 4 — Muy alta | 242 líneas, múltiples estados, dependencias |

## Políticas (Policies)
| Modelo | Policy | Patrón |
|--------|--------|--------|
| `Oficina` | `OficinaPolicy` | `isAdmin()` |
| `Organismo` | `OrganismoPolicy` | `isAdmin()` (4 métodos) |
| `TipoVehiculo` | `TipoVehiculoPolicy` | `isSuperAdmin()` (más restrictivo) |
| `EstadoPaloma` | `EstadoPalomaPolicy` | `isAdmin()` + `HasPermisos()` fallback |
| `Rol` | `RolPolicy` | `isAdmin()` |

## Modelos migrados recientemente
| Modelo | Tabla | fillable | Relación bloqueo |
|--------|-------|----------|-----------------|
| `Organismo` | `organismos` | `['name']` | `novedades()` → hasMany(News) |
| `TipoVehiculo` | `tipos_vehiculo` | `['nombre', 'activo']` | `vehiculos()` → hasMany(Vehiculo) |
| `EstadoPaloma` | `estados_paloma` | `['nombre', 'color', 'activo']` | `palomas()` → hasMany(Paloma) |

## Notas de rutas web.php
- Rutas migradas: `Route::get('/ruta', function () { return view('livewire.componente.layout'); })->name('componente.index');`
- `use` del controlador eliminado de web.php
- Controlador eliminado del filesystem tras verificar 0 referencias en proyecto
- ⚠️ Bug preexistente: `EstadoPalomaController` usaba `redirect()->route('admin.estados-paloma.index')` pero la ruta real es `admin.palomar.estados-paloma.index`
- Consumidores externos (VehiculoController, GuardiaController, EditarNovedadModal) usan Eloquent directo (`::where()->get()`), NO rutas de controller
