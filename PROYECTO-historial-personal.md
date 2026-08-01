# Historial de Personal — Grados, Pases, Comisiones, Altas/Bajas

Referencia de lo que estamos construyendo para dejar de tratar el grado y
la unidad de un usuario como atributos fijos, y empezar a tratarlos como
líneas de tiempo (historial). Ir marcando acá a medida que avanza.

## Idea general

- `users.grado_id` y `users.unidad_id` siguen existiendo tal cual están,
  pero pasan a ser **campos cacheados** (el valor vigente/actual), no la
  fuente de verdad. Se actualizan solos (ver hooks de cada modelo abajo).
- El historial completo vive en tablas nuevas, separadas por concepto:
  - `historial_grados` → ascensos y degradaciones.
  - `pases` → cambios de unidad formal (**ojo**: originalmente se pensó
    como una sola tabla `destinos` con pases y comisiones juntos — se
    separó, ver nota en la sección 2).
  - `comisiones` → servicio transitorio en otra unidad, sin dejar la
    unidad formal.
  - `historial_estado` (altas/bajas) → si está activo en el Ejército o no.
- El "valor vigente" de cada tabla es la fila más reciente / la fila con
  `fecha_hasta`/`fecha_fin` en `null`, según corresponda.

---

## 1. Historial de Grados — ✅ Completo

- [x] Migración `historial_grados` (`user_id`, `grado_id`, `tipo`
      ascenso/degradacion, `numero_orden`, `fecha_cambio`, `resolucion`,
      `observaciones`).
- [x] Modelo `HistorialGrado` (`app/Models/HistorialGrado.php`).
- [x] Relación `User::historialGrados()` y helper `User::ultimoCambioGrado()`.
- [x] `UserForm::save()` crea automáticamente una fila en `historial_grados`
      cada vez que cambia `grado_id`, detectando ascenso/degradación
      comparando el campo `orden` de `Grado` (en esta app, `orden` menor =
      jerarquía más alta; Coronel = 3, Sdo. 1° = 15).
- [x] Usuario nuevo arranca con `grado_id` preseleccionado en Sdo. 1°
      (el de `orden` más alto entre los activos), pero editable — para
      contemplar pases que ingresan con otro grado.
- [x] **Backfill**: migración `backfill_historial_grados_iniciales` — crea
      la primera fila de `historial_grados` para usuarios existentes,
      usando `created_at` como fecha aproximada (marcado en
      `observaciones` como carga retroactiva). Excluye al superadmin
      (`whereNotNull('grado_id')`). Idempotente y reversible.
- [x] **Panel de edición**: `HistorialGradosPanel`
      (`app/Livewire/Admin/HistorialGradosPanel.php` +
      `resources/views/livewire/admin/historial-grados-panel.blade.php`),
      embebido en `edit.blade.php`. Permite a admin/superadmin cargar
      `numero_orden` (formato libre, ej. `016/2026`), `resolucion` y
      `observaciones` de una fila ya existente. No permite tocar `tipo`,
      `fecha_cambio` ni `grado_id` — esos tres siguen siendo responsabilidad
      exclusiva del hook automático en `UserForm::save()`.
- [x] **Deuda técnica resuelta**: `calcularDigitoVerificadorCi()` pasó a
      `public static` en `User.php`; `UserForm::calcularDigitoVerificador()`
      ahora llama a ese método en vez de reimplementar el algoritmo.

---

## 2. Pases y Comisiones

> **Nota de diseño**: originalmente se planeó como una sola tabla
> `destinos` con un campo `tipo` (`destino`/`comision`). Se decidió
> separar en dos tablas independientes: `pases` (reemplaza la unidad
> formal) y `comisiones` (servicio transitorio, convive con la unidad
> formal). Además, al intentar crear la tabla `destinos` se descubrió
> que **ya existía una tabla con ese nombre**, sin relación con esto
> (es sobre destinatarios de comunicaciones/novedades) — motivo extra
> para el cambio de nombre a `pases`.

### 2.a Pases — ✅ Completo

- [x] Migración `pases` (`user_id`, `unidad_id` [FK explícita a
      `unidades`, ojo con el pluralizador de Laravel — ver Notas
      sueltas], `fecha_desde`, `fecha_hasta` [null=vigente],
      `numero_orden` [texto libre, ej. "O.B. N° 006/2026"], `motivo`).
- [x] Modelo `Pase` (`app/Models/Pase.php`).
- [x] `Pase::fechaDesdeParaPase($fechaProduccion)`: dado el día en que
      se produce el pase, calcula el 1° del mes siguiente — regla de
      negocio: la unidad anterior cierra el último día del mes, la
      nueva arranca el 1° del mes siguiente. Sin overlap ni hueco.
- [x] Hook `created()`: cierra automáticamente el pase anterior vigente
      del usuario (`fecha_hasta` = `fecha_desde` del nuevo - 1 día) y
      sincroniza `users.unidad_id` con la nueva unidad.
- [x] Hook `creating()`: **bloquea la creación si el usuario tiene una
      comisión vigente** — hay que cerrarla a mano primero (ver 2.b).
- [x] Relación `User::pases()` y helper `User::paseVigente()`.
- [x] Panel `PasePanel` (`app/Livewire/Admin/PasePanel.php` +
      `resources/views/livewire/admin/pase-panel.blade.php`), embebido
      en `edit.blade.php`. Formulario: fecha, unidad destino,
      número de orden (obligatorio), motivo (opcional).
- [x] Backfill: migración `backfill_pases_inicial` — un pase inicial
      por usuario existente, con `fecha_desde` = `created_at` **tal
      cual** (sin pasar por `fechaDesdeParaPase()`, para no correr la
      fecha real un mes de más). Excluye superadmin y usuarios sin
      `unidad_id`.

### 2.b Comisiones — ⏳ Falta el panel

- [x] Migración `comisiones` (`user_id`, `unidad_id`, `fecha_inicio`,
      `fecha_fin` [null=vigente], `tipo_orden`, `numero_orden`, `motivo`).
      `tipo_orden` es texto libre a nivel de base, pero el panel debería
      ofrecer un `<select>` con: `O.B.`, `O.Bn.`, `O.C.G.E.`, `Otros`
      (constante `Comision::TIPOS_ORDEN`).
- [x] Modelo `Comision` (`app/Models/Comision.php`).
- [x] Hook `creating()`: bloquea si el usuario ya tiene una comisión
      vigente (exclusividad — no se puede estar en dos comisiones a la
      vez).
- [x] Relación `User::comisiones()` y helper `User::comisionVigente()`.
- [x] Regla cruzada con `Pase` ya implementada (ver 2.a): si la unidad
      donde está en comisión quiere absorberlo como pase formal, hay
      que cerrar la comisión (`fecha_fin`) a mano antes de poder crear
      el `Pase`. No se cierra sola automáticamente.
- [ ] **Falta el panel de UI** (`ComisionPanel`, mismo patrón que
      `PasePanel`/`HistorialEstadoPanel`) para que un admin pueda abrir
      y cerrar comisiones desde `edit.blade.php`.
- [ ] No sincroniza nada en `users` a propósito — es informativa. Queda
      pensado para más adelante: partes de fuerza numéricos (quién está
      físicamente en cada unidad hoy, sea efectivo o en comisión), que
      se resuelven con una query sobre esta tabla cuando se necesite.

---

## 3. Altas/Bajas (historial_estado) — ✅ Completo

- [x] Migración `historial_estado` (`user_id`, `tipo` alta/baja,
      `fecha`, `motivo`).
- [x] Modelo `HistorialEstado` (`app/Models/HistorialEstado.php`).
- [x] Regla de negocio: máximo `HistorialEstado::MAX_ALTAS` (3) altas
      por usuario — 1 ingreso + hasta 2 reingresos, nunca más. Se
      valida en el hook `creating()`, lanza `ValidationException`.
- [x] Sincronización en un solo sentido: `historial_estado` manda sobre
      `users.status` (hook `created()`). Una baja pone `status =
      inactive`; una alta (ingreso o reingreso) pone `status = active`.
      **No** es lo mismo que el soft-delete de `users` (`deleted_at`) —
      son dos sistemas de "usuario deshabilitado" que no se sincronizan
      entre sí (ver Notas sueltas).
- [x] `users.status` pasó de `VARCHAR` libre a `ENUM('active','inactive')`
      a nivel de base, más un enum PHP `App\Enums\UserStatus` con cast
      en `User::casts()`.
- [x] Relación `User::historialEstados()` y helpers `User::ultimoEstado()`,
      `User::estaActivoEnElEjercito()`, `User::altasRestantes()`.
- [x] Panel `HistorialEstadoPanel` (`app/Livewire/Admin/HistorialEstadoPanel.php`
      + `resources/views/livewire/admin/historial-estado-panel.blade.php`).
      El tipo del próximo movimiento se infiere solo (no se elige por
      select): si está activo, solo aparece "Dar de baja"; si está de
      baja, solo "Registrar reingreso" (deshabilitado si ya usó las 3
      altas).
- [x] Backfill: migración `backfill_historial_estado_inicial` — una
      alta inicial por usuario existente (fecha = `created_at`), usando
      `HistorialEstado::withoutEvents()` para no pisar el `status` que
      ya tenía cada usuario. Excluye superadmin.

---

## 4. Wizard de alta de usuario — ✅ Completo (versión base)

Reemplaza el formulario de un solo paso por un flujo de 3 pasos, solo
para **crear** usuarios nuevos (`admin.users.create`). Editar sigue
usando `UserForm` de siempre, con los 3 paneles de historial colgados
debajo en `edit.blade.php`.

- [x] Componente `UserWizard` (`app/Livewire/Admin/UserWizard.php`) +
      vista (`resources/views/livewire/admin/user-wizard.blade.php`).
- [x] **Paso 1 — Cédula**: valida C.I. única, crea el `User` ya mismo
      (solo con `ci` cargada). A partir de acá los pasos siguientes
      actúan sobre este mismo registro.
- [x] **Paso 2 — Grado/Unidad**: tipo `alta` (Sdo. 1° automático) o
      `pase` (select libre de grado). Una sola fecha alimenta las tres
      tablas: `historial_grados` (tipo `ascenso`, sin grado anterior),
      `historial_estado` (tipo `alta`), y `pases` — con la distinción
      de `fecha_desde` ya confirmada: **literal** si es alta (ingreso
      real), **regla de mes** (`Pase::fechaDesdeParaPase()`) si es pase.
- [x] **Paso 3 — Datos personales**: nombre, apellidos, fecha de
      nacimiento, email, contraseña, roles. Recién acá se marca
      `perfil_completo_at` (ver punto de riesgo abajo).
- [x] Migración: `name`, `last_name`, `email`, `password` pasaron a
      `nullable` en `users` (para poder crear el registro en el Paso 1
      sin esos datos todavía), + columna `perfil_completo_at`
      (timestamp nullable).
- [x] Indicador de pasos visual (círculos numerados + conector), clases
      `.wizard-*` agregadas como addendum a `ops-panel.css` (reusa
      `.btn-ops-primary` para el botón de avanzar, no duplica gradiente).

### Pendiente / riesgos conocidos del wizard

- [ ] **Usuarios abandonados a mitad de wizard**: si alguien completa
      el Paso 1 y no llega al Paso 3, queda un `User` real en la base
      con `name`/`email` en null y `perfil_completo_at` en null, que
      rompe el listado (`index.blade.php` espera esos campos). Decisión
      pendiente: ¿ocultar/filtrar esos usuarios del listado con un
      badge "Incompleto" + botón para retomar, o un comando de limpieza
      que los borre después de X días sin completar? **No implementado
      todavía.**
- [ ] No incluye la pestaña de Dirección ni el toggle `is_super_admin`
      (siguen disponibles editando al usuario después desde `UserForm`).
- [ ] El camino "pase" del wizard no contempla comisión vigente (no
      debería aplicar para un usuario recién creado, pero repasar si
      alguna vez se permite "importar" un usuario con historial previo).

---

## Fixes colaterales encontrados en el camino (no eran el objetivo, pero bloqueaban)

- [x] `User::casts()` no tenía `fecha_nacimiento` como `date` — rompía
      `UserForm::mount()` (`->format()` sobre un string) apenas un
      usuario real (creado por el wizard) tuvo esa fecha cargada. Ya
      tenía el bug antes del wizard, solo que ningún usuario de prueba
      lo había disparado todavía.
- [x] Foreign key `documentos.subido_por` → `users.id` no tenía
      `ON DELETE`, bloqueaba el `forceDelete()` de cualquier usuario
      con documentos subidos (SQLSTATE 1451). Se cambió a
      `ON DELETE SET NULL`: el documento se conserva, se pierde el
      dato de quién lo subió originalmente. El archivo físico en
      storage nunca se toca por este cambio.
- [x] Bug del pluralizador de Laravel (ya conocido de antes en este
      proyecto): `constrained()` sin argumento sobre `unidad_id` intenta
      buscar la tabla `unidads` en vez de `unidades`. Hay que pasar
      siempre `constrained('unidades')` explícito en cualquier
      migración nueva que tenga una FK a esa tabla.

---

## Notas sueltas

- Registro público (`Features::registration()` de Fortify) quedó
  apagado de nuevo — alta de usuarios solo por el panel.
- `puedeEditarDatosBasicos()` en `UserForm` (C.I. → Segundo Apellido,
  Email, Contraseña y Roles bloqueados salvo admin/superadmin) sigue
  andando bien, sin cambios.
- Los paneles de historial (`HistorialGradosPanel`, `PasePanel`,
  `HistorialEstadoPanel`) quedaron como clases con namespace en
  `app/Livewire/Admin/` — **no** como Blade components anónimos en
  `resources/views/components/` (que es el patrón real de
  `salidas-vehiculo`, descubierto por accidente vía `grep`). Decisión
  consciente: se dejaron así para no arriesgar romper algo que ya
  andaba, en vez de migrar todo a un único patrón.
- Todos los paneles nuevos comparten el mismo criterio de permisos:
  admin o superadmin (`isSuperAdmin() || roles->contains('name', 'admin')`).
  Soft-delete y `forceDelete` de usuarios siguen siendo exclusivos de
  superadmin (sin cambios, ya estaba así en `UserController`).