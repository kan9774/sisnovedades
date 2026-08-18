# AUDITORÍA — Reglas "Never do" del AGENTS.md

**Fecha:** 2026-08-17  
**Repositorio:** C:/laragon/www/novedades (main)  
**Alcance:** Violaciones existentes en código actual (no hipotéticas)

---

## Tabla de hallazgos

| Regla violada | Archivo:línea | Descripción del problema | Severidad | Sugerencia de fix | Estado |
|---------------|---------------|--------------------------|-----------|-------------------|--------|
| ~~**Never do: forceDelete() sin limpiar FK RESTRICT**~~ | `app/Livewire/Admin/Users.php:256` — `destroyIncompleto()` ~~Limpia solo 4 relaciones CASCADE (`historialGrados`, `historialEstados`, `pases`, `comisiones`) pero NO las 4 FKs RESTRICT hacia `users.id`: `historial_palomas.user_id`, `entregas.usuario_id`, `movimientos.usuario_id`, `novedades_personal.user_id`. Mismo bug que el reportado históricamente en `ejecutarEliminacionPermanente()`.~~ | ~~**ALTA**~~ ~~Agregar al inicio del método: `HistorialPaloma::where('user_id', $user->id)->delete();` + `Entrega::where('usuario_id', $user->id)->delete();` + `Movimiento::where('usuario_id', $user->id)->delete();` + `NovedadPersonal::where('user_id', $user->id)->delete();`~~ | ~~**ALTA**~~ | ~~Agregar al inicio del método:~~ `DB::table('historial_palomas')->where('user_id', $user->id)->delete();` + `DB::table('entregas')->where('usuario_id', $user->id)->delete();` + `DB::table('movimientos')->where('usuario_id', $user->id)->delete();` + `DB::table('novedades_personal')->where('user_id', $user->id)->delete();` | ✅ **Resuelto** 2026-08-17 — Se agregaron las 4 líneas DELETE de FK RESTRICT + 4 de CASCADE dentro de `DB::transaction()`, reusando el patrón de `ejecutarEliminacionPermanente()`. Test: `destroy incompleto clears FK RESTRICT tables` |
| ~~**Never do: forceDelete() sin limpiar FK RESTRICT**~~ | `app/Livewire/Admin/Users.php:289` — `ejecutarEliminacionPermanenteIncompleto()` ~~Código duplicado del anterior (línea 256). Mismas FKs RESTRICT sin limpiar.~~ | ~~**ALTA**~~ ~~Eliminar este método duplicado o corregir con las mismas 4 líneas DELETE que el método de la línea 256.~~ | ~~**ALTA**~~ | ~~Eliminar este método duplicado o corregir con las mismas 4 líneas DELETE que el método de la línea 256.~~ | ✅ **Resuelto** 2026-08-17 — Se aplicó el mismo patrón de limpieza de FKs RESTRICT + CASCADE que `ejecutarEliminacionPermanente()`. Test: `ejecutar eliminacion permanente incompleto desde papelera limpia FK RESTRICT` |
| ~~**Never do: SQL crudo en migración sin bifurcación por driver**~~ | `database/migrations/2026_07_30_200117_migrate_grade_to_grados.php:53` — `down()` ~~`->update(['users.grade' => DB::raw('grados.nombre')])` con JOIN entre `users` y `grados`. UPDATE con JOIN tiene sintaxis diferente entre MySQL y Postgres. Fallará al correr `migrate:rollback` en Postgres.~~ | ~~**ALTA**~~ ~~Bifurcar: `if (DB::getDriverName() === 'pgsql')` usar `DB::statement('UPDATE users SET grade = grados.nombre FROM grados WHERE users.grade = grados.id')` / else usar el query builder actual.~~ | ~~**ALTA**~~ | ~~Bifurcar: `if (DB::getDriverName() === 'pgsql')` usar `DB::statement('UPDATE users SET grade = grados.nombre FROM grados WHERE users.grado_id = grados.id')` / else usar el query builder actual.~~ | ✅ **Resuelto** 2026-08-17 — Se bifurcó `down()` con `DB::getDriverName() === 'pgsql'`. Postgres usa `UPDATE users SET grade = grados.nombre FROM grados WHERE users.grado_id = grados.id`, MySQL usa el query builder con `join()`. El `up()` no tiene problema (usa `->update()` sin JOIN, solo `where()`). |
| **Never do: SQL crudo en migración sin bifurcación por driver** | `database/migrations/2026_08_01_180138_backfill_perfil_completo_at_inicial.php:25` | `->update(['perfil_completo_at' => DB::raw('created_at')])` sin bifurcación. Riesgo bajo (columna estándar), pero viola la regla del proyecto. | **BAJA** | Bifurcar por driver o reemplazar con `DB::table('users')->update(['perfil_completo_at' => DB::raw('`created_at`')])` con backticks para MySQL / comillas dobles para Postgres. |
| **Never do: borrar controlador legacy sin verificar 0 referencias** | `app/Http/Controllers/VehiculoController.php` | Archivo existe con 280+ líneas de código CRUD completo pero **no tiene rutas registradas**. Las 5 vistas Blade en `admin/vehiculos/` referencian rutas `admin.vehiculos.*` que no existen → darían error 404. | **MEDIA** | O bien registrar las rutas en `web.php` y reactivar el controller, o eliminar el archivo + las 5 vistas Blade rotas tras confirmar que el Livewire `vehiculos/layout` las reemplazó todas. | ✅ **Resuelto** 2026-08-17 — Se eliminó `VehiculoController.php` + 4 vistas rotas (`create`, `edit`, `index`, `show`). Confirmado 0 rutas en `web.php`, 0 referencias en Livewire. El CRUD completo opera vía `livewire.vehiculos` (MantenimientoModal). |
| **Never do: borrar controlador legacy sin verificar 0 referencias** | `app/Http/Controllers/MantenimientoVehiculoController.php` | No estaba en la lista original del AGENTS.md pero existe con 2 rutas activas (`admin.vehiculos.mantenimientos.index`, `destroy`) y referencia la vista `admin/vehiculos/mantenimientos/index.blade.php`. | **MEDIA** | Verificar si el CRUD de mantenimientos migró a Livewire. Si sí, eliminar controller + ruta + vista. Si no, mantener. | ✅ **Resuelto** 2026-08-17 — El CRUD de mantenimientos migró a Livewire (`app/Livewire/Vehiculos/MantenimientoModal.php` con create/edit/list/delete). Se eliminó el controller, la vista old, las 2 rutas de `web.php` y el import. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/documentos/index.blade.php:144-432` | 7 modales Bootstrap (formulario crear/editar, 2x eliminación, vista previa, papelera). Deberían usar `wire:confirm` para eliminar + `x-ops-card` + `x-teleport="body"` para formularios. | **MEDIA** | Migrar modales de eliminación a `wire:confirm` simple. Migrar modales de formulario a patrón `x-ops-card` + `x-teleport="body"`. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/vehiculos/index.blade.php:928-995` | 2 modales de eliminación (soft delete + force delete) con Bootstrap modal. Deberían ser `wire:confirm` simples. | **BAJA** | Reemplazar modales por `wire:confirm="¿Está seguro?"` en los botones de eliminar. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/conductores/index.blade.php:385-410` | 1 modal Bootstrap de confirmación de eliminación. Debería ser `wire:confirm`. | **BAJA** | Reemplazar por `wire:confirm`. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/roles/index.blade.php:223-245` | 1 modal Bootstrap de confirmación de eliminación. Debería ser `wire:confirm`. | **BAJA** | Reemplazar por `wire:confirm`. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/permisos/index.blade.php:207-229` | 1 modal Bootstrap de confirmación de eliminación. Debería ser `wire:confirm`. | **BAJA** | Reemplazar por `wire:confirm`. |
| **Never do: window.confirm() nativo** | `resources/views/livewire/backup-manager.blade.php:163` | `onclick="return confirm('...')"` nativo. Debería usar `wire:confirm`. | **BAJA** | Reemplazar por `wire:confirm`. |
| **Never do: window.confirm() nativo** | `resources/views/livewire/catalogos/catalogo-simple-modal.blade.php:49` | `onclick="return confirm('Eliminar?')"` nativo. | **BAJA** | Reemplazar por `wire:confirm`. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/pdf-destinatarios.blade.php:140-267` | 2 modales Bootstrap (crear/editar grupo, asignar usuarios). Deberían usar `x-ops-card` + `x-teleport="body"`. | **MEDIA** | Migrar al patrón `x-ops-card`. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/salidas-pendientes.blade.php:70-127` | 1 modal Bootstrap (boleta de cierre). Debería usar `x-ops-card` + `x-teleport="body"`. | **BAJA** | Migrar al patrón `x-ops-card`. |
| **Never do: modales custom en Livewire ya migrado** | `resources/views/livewire/admin/csm-panel.blade.php:31-64` | 1 modal Bootstrap (fecha de firma contrato). Debería usar `x-ops-card` + `x-teleport="body"`. | **BAJA** | Migrar al patrón `x-ops-card`. |

---

## Resumen por categoría

| Categoría | Hallazgos | Alta | Media | Baja |
|-----------|-----------|------|-------|------|
| forceDelete() sin FK RESTRICT | 2 | ~~2~~ 0 | 0 | 0 |
| SQL crudo sin bifurcación driver | 2 | ~~1~~ 0 | 0 | 1 |
| Controladores legacy con referencias | 2 | ~~2~~ 0 | 0 | 0 |
| Modales custom / window.confirm() | 10 | 0 | 3 | 7 |
| Tests skip/incomplete | 0 | 0 | 0 | 0 |
| **TOTAL** | **16** | ~~3~~ 0 | **3** | **8** |

---

## Correcciones realizadas 2026-08-17

### 1. Users.php — `destroyIncompleto()` y `ejecutarEliminacionPermanenteIncompleto()`
- Se agregaron las 4 FKs RESTRICT que faltaban: `historial_palomas`, `entregas`, `movimientos`, `novedades_personal`
- Se unificó el patrón con `ejecutarEliminacionPermanente()` (DB::table directo en vez de relaciones Eloquent)
- Se agregaron 4 FKs CASCADE: `historial_grados`, `historial_estado`, `pases`, `comisiones`
- **Tests nuevos:** `destroy incompleto clears FK RESTRICT tables` + `ejecutar eliminacion permanente incompleto desde papelera limpia FK RESTRICT`

### 2. Migración 2026_07_30_200117 — `down()` bifurcada por driver
- Postgres: `UPDATE users SET grade = grados.nombre FROM grados WHERE users.grado_id = grados.id`
- MySQL: query builder con `join()` (sin cambios)
- El `up()` no tiene problema (usa `->update()` sin JOIN)

### 3. VehiculoController y MantenimientoVehiculoController — eliminación de controladores huérfanos
- **VehiculoController.php:** confirmado 0 rutas en `web.php`, 0 referencias en Livewire. Se eliminó el controlador + 4 vistas rotas (`create`, `edit`, `index`, `show`). El CRUD completo opera vía `livewire.vehiculos` (componente Livewire con `MantenimientoModal`).
- **MantenimientoVehiculoController.php:** confirmado que el CRUD de mantenimientos migró a Livewire (`app/Livewire/Vehiculos/MantenimientoModal.php` con create/edit/list/delete). Se eliminó el controller, la vista old (`admin/vehiculos/mantenimientos/index.blade.php`), las 2 rutas de `web.php` y el import.

---

## Prioridad de corrección sugerida

1. **ALTA** — `Users.php:256` y `Users.php:289`: forceDelete() sin limpiar FKs RESTRICT. Es el bug histórico documentado que ya rompió producción antes.
2. **ALTA** — Migración `2026_07_30_200117` down(): UPDATE con JOIN sin bifurcación. Fallará en Postgres al rollback.
3. **MEDIA** — `documentos/index.blade.php`: 7 modales Bootstrap → patrón estándar.
4. **BAJA** — Restante de modales custom y window.confirm() (9 casos).

---

## Notas

- **Tests:** No se encontraron tests skip, incomplete ni comentados. La suite está limpia.
- **Controladores eliminados correctamente:** UserController, OficinaController, OrganismoController, TipoVehiculoController, EstadoPalomaController, PermisoController, RolController, ConductorController, PalomarController, PalomaController, VueloController, VehiculoController, MantenimientoVehiculoController — todos eliminados y sin referencias residuales.
- **GuardiaController:** Tiene 3 métodos residuales activos (show, Hoy, pdf) con 4 rutas. No se puede eliminar hasta migrar las vistas Blade correspondientes.
- Las migraciones con `DB::statement()` que SÍ tienen bifurcación por driver (3 archivos) están correctas y no se reportan.
