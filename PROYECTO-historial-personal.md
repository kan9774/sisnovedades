# Historial de Personal — Grados, Destinos, Altas/Bajas

Referencia de lo que estamos construyendo para dejar de tratar el grado y
la unidad de un usuario como atributos fijos, y empezar a tratarlos como
líneas de tiempo (historial). Ir marcando acá a medida que avanza.

## Idea general

- `users.grado_id` y `users.unidad_id` siguen existiendo tal cual están,
  pero pasan a ser **campos cacheados** (el valor vigente/actual), no la
  fuente de verdad.
- El historial completo vive en tablas nuevas, separadas por concepto:
  - `historial_grados` → ascensos y degradaciones.
  - `destinos` → dónde presta servicio (pases y comisiones juntos, con
    un campo `tipo` para distinguirlos).
  - `historial_estado` (altas/bajas) → si está activo en el Ejército o no.
- El "valor vigente" de cada tabla es la fila más reciente por fecha para
  ese `user_id`.

---

## 1. Historial de Grados — ✅ Base andando

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

### Pendiente en Historial de Grados

- [x] Migración de datos: crear la primera fila de `historial_grados`
      para los usuarios que ya existen hoy (si no, quedan sin historial
      hasta su próximo cambio de grado).
- [ ] Pantalla dedicada para cargar/editar `numero_orden`, `resolucion` y
      `observaciones` de un cambio de grado (`UserForm::save()` los deja
      en `null`, no forman parte de ese formulario).
- [ ] Deuda técnica detectada (no bloqueante): `User.php` ya tenía su
      propia lógica de dígito verificador de C.I. (`ci_dv`,
      `setCiAttribute()`, `ci_formateado`) *antes* de que se agregara
      `calcularDigitoVerificador()` en `UserForm.php` — quedó duplicado.
      Pendiente: sacar la copia de `UserForm.php` y usar directamente
      `$user->ci_dv` / `$user->ci_formateado` en el blade.

---

## 2. Destinos (pases y comisiones) — ⏳ Sin empezar

Idea acordada: una sola tabla `destinos`, no una por "pase" y otra por
"comisión" — la diferencia es solo el campo `tipo`:

- `user_id`, `unidad_id`, `tipo` (`destino` | `comision`), `fecha_desde`,
  `fecha_hasta` (null = vigente), `motivo`.
- Un **pase** cambia la unidad de pertenencia formal (cierra el destino
  anterior).
- Una **comisión** puede convivir con el destino de origen: la persona
  sigue perteneciendo a su unidad, pero trabaja transitoriamente en otra
  (ej: alguien de otra unidad que trabaja en B.Com.N°1).

### Por decidir / hacer

- [ ] Migración `destinos`.
- [ ] Modelo `Destino`.
- [ ] Relación en `User` (`destinos()`, `destinoVigente()`,
      `comisionVigente()` si aplica).
- [ ] Definir cómo conviven `unidad_id` (destino formal) y una comisión
      activa a la hora de mostrar "dónde trabaja" en listados/menús.
- [ ] Enganchar la escritura en `UserForm` (o una pantalla aparte, a
      definir).

---

## 3. Altas/Bajas (historial_estado) — ⏳ Sin empezar

- `user_id`, `tipo` (`alta` | `baja`), `fecha`, `motivo`.
- Concepto distinto a "está activo en la app" (`users.status`): esto es
  si está activo en el Ejército o no.

### Por decidir / hacer

- [ ] Migración `historial_estado`.
- [ ] Modelo.
- [ ] Definir relación con `users.status` (¿se sincronizan automáticamente,
      o son cosas separadas que un admin carga a mano?).

---

## Notas sueltas

- Registro público (`Features::registration()` de Fortify) quedó
  apagado de nuevo — alta de usuarios solo por el panel.
- `puedeEditarDatosBasicos()` en `UserForm` (C.I. → Segundo Apellido,
  Email, Contraseña y Roles bloqueados salvo admin/superadmin) quedó
  resuelto y anda bien — no debería verse afectado por lo de arriba,
  pero conviene revisarlo si en algún momento el grado/unidad se editan
  desde una pantalla separada de historial en vez de acá.
