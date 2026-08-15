# Análisis del Sistema de Autorización

> Generado: 2026-08-14
> Última actualización: 2026-08-15
> Proyecto: novedades (Laravel 13 + Livewire 3 + Spatie Permission custom)

---

## 1. Inventario de Policies existentes

**31 Policies** registradas en `AppServiceProvider` via `Gate::policy()`.

### Policies por patrón

#### Patrón CRUD estándar (viewAny/view/create/update/delete + restore/forceDelete)

| Policy | Modelo | Métodos | Notas |
|--------|--------|---------|-------|
| `VehiculoPolicy` | `Vehiculo` | viewAny, view, create, update, delete, restore, forceDelete | SoftDeletes |
| `TipoVehiculoPolicy` | `TipoVehiculo` | viewAny, view, create, update, delete, restore, forceDelete | SoftDeletes |
| `DocumentoPolicy` | `Documento` | viewAny, view, create, update, delete, restore, forceDelete | Usa `Response` en vez de `bool` |
| `ConductorPolicy` | `Conductor` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `PalomarPolicy` | `Palomar` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `PalomaPolicy` | `Paloma` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `VueloPolicy` | `Vuelo` | viewAny, create, update, delete | Sin `view` ni SoftDeletes |
| `UnidadPolicy` | `Unidad` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `CategoriaPolicy` | `Categoria` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `UbicacionPolicy` | `Ubicacion` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `ProveedorPolicy` | `Proveedor` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `TallaPolicy` | `Talla` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `GradoPolicy` | `Grado` | viewAny, view, create, update, delete | Sin SoftDeletes |
| `OficinaPolicy` | `Oficina` | viewAny, view, create, update, delete | delete solo admin (no delegable) |
| `OrganismoPolicy` | `Organismo` | viewAny, create, update, delete | Sin `view` |
| `EstadoPalomaPolicy` | `EstadoPaloma` | viewAny, create, update, delete | Sin `view` |
| `CategoriaDocumentoPolicy` | `CategoriaDocumento` | viewAny, create, update, delete | update/delete sin parámetro modelo |
| `PermissionPolicy` | `Permission` | viewAny, create, update, delete | CRUD completo |
| `RolPolicy` | `Rol` | viewAny, create, update, delete | delete no permite borrar rol "admin" |

#### Policies con métodos custom

| Policy | Modelo | Métodos custom | Descripción |
|--------|--------|----------------|-------------|
| `GuardiaPolicy` | `Guard` | cerrar, reactivar, view, viewTrashed, delete | Lógica basada en membresía de guardia + status |
| `NovedadPolicy` | `News` | tomar | viewAny/view siempre true; create/update/delete por membresía |
| `UserPolicy` | `User` | assignPermissions | Protege edición de SuperAdmins |
| `SalidaVehiculoPolicy` | `SalidaVehiculo` | ninguno (CRUD completo) | Acceso por rol operativo (capitán/oficial/escribiente) |
| `ItemPolicy` | `Item` | marcarEnReparacion, volverDeReparacion | CRUD + acciones de unidad |
| `ItemUnidadPolicy` | `ItemUnidad` | asignar, marcarEnReparacion, darDeBaja | CRUD + acciones específicas |
| `MovimientoPolicy` | `Movimiento` | registrarEntrada, registrarSalida, registrarTransferencia, registrarAjuste | No sigue patrón CRUD |
| `EntregaPolicy` | `Entrega` | solo create (además de viewAny/view) | CRUD incompleto |
| `GuardiaPdfDestinatarioPolicy` | `GuardiaPdfDestinatario` | before() + create, update, delete | `before()` exime a SuperAdmin y miembros de guardia del día |

### ¿Hay Policies huérfanas?

**No.** Las 31 Policies están registradas en `Gate::policy()` en `AppServiceProvider`. No hay ninguna Policy sin registro.

---

## 2. Inventario de permisos

**~173 permisos** definidos en `PermisoSeeder`, organizados en 30 módulos.

### Permisos que NO siguen patrón CRUD estándar

| Permiso | Módulo | Usado en Policy/Gate | Estado |
|---------|--------|---------------------|--------|
| `cerrar_guardia` | Guardia | GuardiaPolicy::cerrar() | OK |
| `ver_pdf_guardia` | Guardia | Gate directo (AppServiceProvider) | OK (gate) |
| `tomar_novedad` | Novedad | NovedadPolicy::tomar() | OK |
| `registrar_entrada_inventario` | Inventario | MovimientoPolicy::registrarEntrada() | OK |
| `registrar_salida_inventario` | Inventario | MovimientoPolicy::registrarSalida() | OK |
| `registrar_transferencia_inventario` | Inventario | MovimientoPolicy::registrarTransferencia() | OK |
| `ajustar_stock_inventario` | Inventario | MovimientoPolicy::registrarAjuste() | OK |
| `asignar_item_unidad` | Inventario | ItemUnidadPolicy::asignar(), EntregaPolicy::create() | OK |
| `reparar_item_unidad` | Inventario | ItemPolicy::marcarEnReparacion(), ItemUnidadPolicy::marcarEnReparacion() | OK |
| `dar_baja_item_unidad` | Inventario | ItemUnidadPolicy::darDeBaja() | OK |
| `asignar_destinatarios_pdf` | GuardiaPdfDestinatario | GuardiaPdfDestinatarioPolicy::create() | OK |

### Discrepancias de nombres

**Resuelto (15/08):** `ver_log` vs `ver_logs` — el gate en AppServiceProvider ahora usa `HasPermisos('ver_logs')` (coincide con el seeder).

### Código muerto (permisos en Policy pero no en seeder)

**No encontrado.** Todos los permisos chequeados en Policies están definidos en el seeder.

---

## 3. El campo de agrupación

### Definición

- **Nombre de columna:** `model`
- **Tipo:** `string`, nullable
- **Migración:** `2026_07_28_080343_add_model_to_permissions_table.php`

### Semántica

- **Es por modelo/tabla**, NO por sección de UI/menú
- Ejemplos: "Guardia", "Novedad", "Inventario", "User", "Sistema"
- **No siempre coincide 1:1 con modelos** — algunos módulos del seeder no tienen modelo (ej. "Sistema", "Adjunto", "BoletaCierre", "Resumen")

### Estado de datos

- **Todos los permisos del PermisoSeeder tienen `model` llenado** (usa `updateOrCreate` con `model` como dato)
- Permisos creados manualmente desde la UI de Permisos pueden tener `model` null

### Uso

- **Solo visual** — se usa en `Livewire\Roles::agruparPermisosPorModulo()` para mostrar checkboxes agrupados por módulo en la UI de asignación
- No hay lógica de negocio que lo use para consultas o filtros

---

## 4. Roles y matriz de permisos

### Roles definidos (RolSeeder)

| Rol | Descripción | Permisos explícitos |
|-----|-------------|-------------------|
| `escribiente` | Registra novedades dentro de su guardia asignada | `registrar_novedad` |
| `oficial_de_dia` | Abre la guardia y supervisa el registro de novedades | `crear_guardia`, `registrar_novedad`, `editar_cualquier_novedad`, `cerrar_guardia` |
| `capitan_de_servicio` | Supervisor responsable. Cierra la guardia y tiene permisos totales | `crear_guardia`, `cerrar_guardia`, `registrar_novedad`, `editar_cualquier_novedad`, `eliminar_novedad` |
| `visitante` | Solo puede ver guardias cerradas y sus novedades | Ninguno (solo vistas públicas) |
| `admin` | Acceso irrestricto para mantenimiento del sistema | **Todos los permisos** (sync de todos) |
| `colombofilo` | Encargado del palomar militar | CRUD de palomares |

### Alcance por módulo

| Módulo | Admin | Escribiente | Oficial | Capitán | Colombofilo | Visitante |
|--------|-------|-------------|---------|---------|-------------|-----------|
| **Guardia** | Total | Ver/crear (su guardia) | Crear/cerrar/crear novedad | Crear/cerrar/eliminar | No | Ver cerradas |
| **Novedades** | Total | Crear (su guardia) | Crear/editar cualquier | Crear/editar/eliminar | No | Ver |
| **Inventario** | Total | No | No | No | No | No |
| **Vehículos** | Total | Ver (SalidaVehiculoPolicy) | Ver (SalidaVehiculoPolicy) | Ver (SalidaVehiculoPolicy) | No | No |
| **Palomas/Palomar** | Total | No | No | No | CRUD | No |
| **Usuarios** | Total | No | No | No | No | No |
| **Roles/Permisos** | Total | No | No | No | No | No |
| **Logs** | Total | No | No | No | No | No |

### Permisos por módulo (referencia rápida)

**SalidaVehiculo:** `ver_salida_vehiculo`, `crear_salida_vehiculo`, `editar_salida_vehiculo`, `eliminar_salida_vehiculo`

---

## 5. Gate::before() global — SuperAdmin exento de todo

### Implementación

Desde `AppServiceProvider::boot()`:

```php
Gate::before(function (\App\Models\User $user) {
    return $user->isSuperAdmin() ? true : null;
});
```

**Resuelto (15/08):** Se agregó el `Gate::before()` global. Antes de esto, cada Policy debía recordar explícitamente chequear `isSuperAdmin()` o `isAdmin()`, con estilos inconsistentes entre Policies.

### Implicancias

- **SuperAdmin**: exento de TODOS los gates automáticamente. No necesita permisos explícitos ni pasar por ninguna Policy.
- **Admin (no super)**: NO eximido — depende 100% de permisos asignados (vía `HasPermisos()` en cada Policy o `isAdmin()` en gates explícitos).
- **Policies que mantienen `isAdmin()`**: GuardiaPolicy y UserPolicy aún tienen `isAdmin()` en sus métodos, como decisión explícita (ver sección 6).

### Patrón actual en Policies

**Todas las Policies** (salvo GuardiaPolicy y UserPolicy, que mantienen `isAdmin()` por decisión explícita en 2 métodos cada una) están basadas 100% en permisos, sin atajos de rol:

```php
// SalidaVehiculoPolicy — solo HasPermisos, sin isAdmin()
public function viewAny(User $user): bool
{
    return $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente()
        || $user->HasPermisos('ver_salida_vehiculo');
}
```

Los roles operativos (capitán, oficial, escribiente) acceden por lógica de rol en la Policy. Los permisos explícitos (`HasPermisos`) son un canal alternativo que puede asignarse desde el panel de Roles.

---

## 6. GuardiaPolicy y UserPolicy — isAdmin() explícito por decisión

Solo 2 Policies mantienen `isAdmin()` como atajo:

| Policy | Métodos con `isAdmin()` | Motivo |
|--------|------------------------|--------|
| `GuardiaPolicy` | `view`, `viewTrashed`, `create`, `cerrar`, `reactivar`, `viewAny`, `delete` | El capitan de guardia (que puede no tener permisos explícitos) debe poder gestionar la guardia que lidera |
| `UserPolicy` | `update`, `delete` | Admin puede editar/eliminar usuarios no-SuperAdmin |

En estas 2 Policies, `isAdmin()` actúa como fallback adicional al `HasPermisos()`. SuperAdmin ya está cubierto por `Gate::before()`, pero `isAdmin()` permite acceso a admins con el rol `admin` asignado.

---

## 7. Consistencia de autorización

**Resuelto (15/08):** Antes de implementar `Gate::before()`, las Policies usaban estilos inconsistentes: algunas `isAdmin()`, otras `isSuperAdmin()` explícito. Ahora:

- SuperAdmin → exento globalmente por `Gate::before()`
- Admin → `isAdmin()` solo en GuardiaPolicy y UserPolicy (decisión explícita)
- Resto de Policies → 100% `HasPermisos()`, sin atajos de rol

### Gates explícitos en AppServiceProvider

| Gate | Lógica |
|------|--------|
| `viewAny-user` | `isAdmin() || HasPermisos('ver_usuario')` |
| `viewAny-rol` | `isAdmin()` |
| `viewAny-vehiculo` | `isAdmin() || HasPermisos('ver_vehiculo')` |
| `viewAny-conductor` | `isAdmin() || HasPermisos('ver_conductor')` |
| `viewAny-vuelo` | `isAdmin() || HasPermisos('ver_vuelo')` |
| `viewAny-documento` | `isAdmin() || HasPermisos('ver_documento')` |
| `viewAny-tipos-vehiculo` | `isAdmin() || HasPermisos('ver_tipos_vehiculo')` |
| `view_guardias` | `isAdmin() || isSuperAdmin() || HasPermisos('ver_guardia')` |
| `ver_destinatarios_pdf` | `isAdmin() || HasPermisos('ver_destinatarios_pdf')` |
| `viewAny-log` | `isAdmin() || HasPermisos('ver_logs')` |
| `viewAny-palomar` | `isAdmin() || HasPermisos('ver_palomar')` |
| `viewAny-grado` | `isAdmin() || HasPermisos('ver_grado')` |
| `viewAny-item` | `isAdmin() || HasPermisos('ver_item')` |
| `viewAny-movimiento` | `isAdmin() || HasPermisos('ver_item')` |
| `viewAny-unidad` | `isAdmin() || HasPermisos('ver_item')` |
| `viewAny-entrega` | `isAdmin() || HasPermisos('ver_item')` |
| `viewAny-categoria` | `isAdmin() || HasPermisos('ver_categoria')` |
| `viewAny-talla` | `isAdmin() || HasPermisos('ver_talla')` |
| `viewAny-ubicacion` | `isAdmin() || HasPermisos('ver_ubicacion')` |
| `viewAny-proveedor` | `isAdmin() || HasPermisos('ver_proveedores')` |
| `viewAny-lote` | `isAdmin() || HasPermisos('ver_item')` |
| `delete-attach` | `isAdmin()` |
| Backup operations | `isAdmin()` |

---

## 8. El seeder

### Seeders de auth

| Seeder | Responsable |
|--------|-------------|
| `PermisoSeeder` | ~173 permisos con `updateOrCreate` (idempotente) |
| `RolSeeder` | 6 roles con asignación de permisos |
| `SuperAdminSeeder` | Usuario SuperAdmin |
| `UserSeeder` | Usuarios operativos |
| `DatabaseSeeder` | Orquesta la llamada en orden |

### ¿Genera permisos CRUD automáticamente?

**No.** Los ~173 permisos están escritos a mano en `PermisoSeeder`. No hay generación automática a partir de modelos.

### Idempotencia

| Seeder | Idempotente | Detalle |
|--------|-------------|---------|
| `PermisoSeeder` | S | `updateOrCreate` por nombre ✅ |
| `RolSeeder` | **Parcial** | `firstOrCreate` para roles ✅, pero `sync()` para permisos **pisar** asignaciones manuales hechas desde el panel ❌ |
| `SuperAdminSeeder` | S | `firstOrCreate` ✅ |

**Riesgo:** Correr `RolSeeder` después de asignar permisos manualmente desde el panel **borra las asignaciones manuales** (el `sync()` reemplaza todo).

---

## 9. UI de asignación

### Componentes existentes

| Componente | Ruta | Funcionalidad |
|------------|------|---------------|
| `Livewire\Permisos` | `/permisos` | CRUD de permisos, filtro por módulo, agrupación por `model` |
| `Livewire\Roles` | `/roles` | CRUD de roles, checkboxes de permisos agrupados por módulo |

### UI de Roles (Roles.php)

- Lista de roles con búsqueda (`$search`)
- Formulario inline con modal (create/edit)
- **Checkboxes agrupados por módulo** usando `agruparPermisosPorModulo()` (line 174)
- Agrupación usa el campo `model` del Permission
- El rol "admin" aparece excluido de la lista (`where('name', '!=', 'admin')` en render)
- El rol "admin" tiene todos los permisos asignados automáticamente en el seeder (`sync($todosLosPermisos)`)

### UI de Permisos (Permisos.php)

- CRUD de permisos (crear/editar/eliminar)
- Filtro por nombre/descripción
- Filtro por módulo (`modulos()` — distinct values of `model`)
- Validación de integridad: no permite borrar permisos asignados a roles

---

## Cómo agregar un permiso nuevo

1. **Agregar a `PermisoSeeder`**: incluir el permiso en el array del módulo correspondiente dentro de `$modulos`.
2. **Correr el seeder**: `php artisan db:seed --class=PermisoSeeder`
3. **Asignar al rol admin**: El rol `admin` recibe todos los permisos automáticamente en `RolSeeder` (sync de todos). Si `seeded_permissions_locked` ya es `true`, hay que resetearlo manualmente en la BD o desde el panel antes de correr el seeder, para que el sync incluya el nuevo permiso.
4. **Asignar a otros roles** (opcional): Desde el panel de Roles → editar rol → marcar el nuevo permiso en el grupo correspondiente.
5. **Agregar a Policy**: Si el módulo tiene Policy, agregar el chequeo `HasPermisos('nombre_permiso')` en el método correspondiente. Si no tiene Policy pero se necesita un gate, agregarlo en `AppServiceProvider::boot()` con `Gate::define()`.

---

## Historial de correcciones

### RESUELTO (15/08): Discrepancia `ver_log` vs `ver_logs`
- **Problema:** Gate `viewAny-log` chequeaba `HasPermisos('ver_log')` pero el seeder creaba `ver_logs`.
- **Solución:** Se actualizó el gate en AppServiceProvider para usar `ver_logs`.

### RESUELTO (15/08): Permisos huérfanos en seeder (13 en total)
- **Problema:** 13 permisos definidos en `PermisoSeeder` pero no usados en ninguna Policy/Gate:
  - 6 de SalidaVehiculo: `ver_salidas`, `ver_salida`, `registrar_salida`, `editar_salida`, `eliminar_salida`, `cerrar_salida`
  - 2 de Novedad: `editar_novedad_propia`, `eliminar_novedad_propia`
  - 2 de Guardia: `asignar_escribientes`, `enviar_pdf_guardia`
  - 3 de User: `asignar_permisos_usuario`, `bloquear_usuario`, `resetear_password`
- **Solución:** Eliminados de `PermisoSeeder` y removidos de los roles en `RolSeeder`.

### RESUELTO (15/08): Inconsistencia en permisos de SalidaVehiculo
- **Problema:** 10 permisos para un solo concepto (6 viejos huérfanos + 4 nuevos con nombres redundantes). `ver_salidas_vehiculo` usaba plural inconsistente con el patrón del proyecto.
- **Solución:** Eliminados los 6 viejos. Renombrado `ver_salidas_vehiculo` → `ver_salida_vehiculo` (singular, consistente con `ver_vehiculo`, `ver_guardia`, etc.). Sufijos "(permiso general)" eliminados de las descripciones.

### RESUELTO (15/08): Falta de `Gate::before()` global
- **Problema:** Cada Policy debía recordar chequear `isSuperAdmin()` o `isAdmin()`, con estilos inconsistentes entre Policies.
- **Solución:** Se agregó `Gate::before()` en `AppServiceProvider::boot()` que exime a SuperAdmin de todos los gates. Todas las Policies (salvo GuardiaPolicy y UserPolicy) ahora usan solo `HasPermisos()`.

### RESUELTO (15/08): Columna `seeded_permissions_locked` faltante en BD remota
- **Problema:** `RolSeeder` fallaba con "column seeded_permissions_locked does not exist" en Supabase.
- **Solución:** La migración `2026_08_14_000001_add_seeded_permissions_locked_to_rols_table.php` existía como archivo pero estaba Pending. Se corrió `php artisan migrate`.

---

## Resumen estadístico

| Categoría | Cantidad |
|-----------|----------|
| Policies registradas | 31 |
| Policies con patrón CRUD estándar | ~20 |
| Policies con métodos custom | ~9 |
| Policies con `before()` | 1 (GuardiaPdfDestinatarioPolicy) |
| Policies que usan `Response` | 1 (DocumentoPolicy) |
| Policies con `isAdmin()` explícito | 2 (GuardiaPolicy, UserPolicy) |
| Permisos en seeder | ~173 |
| Módulos en seeder | 30 |
| Roles definidos | 6 |
| Permisos huérfanos | 0 |
| Discrepancias de nombre (seeder vs gate) | 0 |
| Componentes Livewire de auth | 2 (Permisos, Roles) |
