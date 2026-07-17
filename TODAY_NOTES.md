# Notas del día — Livewire + Dashboard + Backup

## ✅ Lo que hicimos hoy

### 6. Sistema de Backup con Panel Livewire
- **Paquete:** `spatie/laravel-backup` v10.3 instalado
- **Configuración:**
  - `config/backup.php` — nombre 'novedades', destino disk `backup`, rotación 7 días
  - `config/filesystems.php` — nuevo disk `backup` → `storage/app/backups`
  - `config/database.php` — `dump_binary_path` apuntando a Laragon MySQL
- **Componente Livewire:** `app/Livewire/BackupManager.php`
  - `quickCreate()` — inicia backup en segundo plano
  - `deleteBackup()` — elimina un backup
  - `runCleanup()` — limpia backups viejos
  - `loadBackups()` — lista backups con fecha, tamaño
- **Vista:** `resources/views/livewire/backup-manager.blade.php`
  - Tarjeta acciones rápidas (Crear, Limpiar, Refrescar)
  - Tabla con lista de backups
  - Info sobre rotación y almacenamiento
- **Controlador:** `app/Http/Controllers/Admin/BackupController.php`
- **Vista admin:** `resources/views/admin/backup.blade.php`
- **Rutas:** GET/POST `/admin/backup` en `routes/web.php`
- **Menú AdminLTE:** agregado item 'Backups' en sección Auditoría
- **Test:** Backup exitoso → `storage/app/backups/B.Com.N°1/2026-07-16-14-58-40.zip`

### 1. Tabla de Salidas de Vehículos — actualizada
- **Archivo:** `resources/views/admin/index.blade.php`
- **Cambios:**
  - Columnas nuevas: Matrícula, Conductor (nombre completo), Hora Salida (H:i), Hora Llegada (H:i)
  - Se eliminaron badges de estado y columnas de combustible/km/litros
  - Se cambió `nombre_corto` → `nombre_completo` para mostrar nombre completo del conductor

### 2. Salidas de Vehículos → componente Livewire reactivo
- **Nuevo componente:** `DashboardSalidas`
  - `app/Livewire/DashboardSalidas.php` (no creado, se usa archivo único)
  - `resources/views/components/⚡dashboard-salidas.blade.php` — clase Livewire
  - `resources/views/livewire/dashboard-salidas.blade.php` — vista del componente
- **Funcionalidad:** `wire:poll.10000ms` (se actualiza cada 10 segundos)
- **Botón de refresh manual** con icono de reload
- **Reemplazó:** la tabla estática en `admin/index.blade.php`

### 3. Correos fallidos — corregidos
- **Clasificación automática de errores** en `app/Jobs/EnviarNovedadGuardiaMail.php`:
  - `⚠️ Casilla llena (quota excedida)` — 552, mailbox full, quota exceeded
  - `❌ Error de autenticación SMTP` — 535, 5.7.1
  - `❌ Error de conexión SMTP` — connection, timeout, refused
  - `❌ Dirección de correo inválida` — invalid address, 553
  - `❓ [mensaje original]` — otros casos
- **Reintentar:** cambiado `dispatch()` → `dispatchSync()` en `correos-fallidos.php`
  - Antes: usaba cola sin worker (emails nunca se enviaban)
  - Ahora: envío inmediato sincrónico

### 4. Limpieza de AdminDashboard
- Se eliminó `$ultimasSalidas` del componente (ya no se usa)
- Se eliminó `use App\Models\SalidaVehiculo;` temporalmente y se recuperó porque se usa en `actualizarDatos()`

### 5. Configuración de polls
- **Salidas:** `wire:poll.10000ms` → 6 requests/minuto
- **Novedades:** `wire:poll.15000ms` → 4 requests/minuto
- **Badge correos fallidos:** `wire:poll.30s` → 2 requests/minuto
- Total: ~12 requests/minuto por navegador (muy liviano)
- Desfasados para evitar picos simultáneos

## 📁 Archivos modificados hoy

```
app/Livewire/AdminDashboard.php          — eliminó $ultimasSalidas, recuperó use SalidaVehiculo
app/Jobs/EnviarNovedadGuardiaMail.php    — añadió clasificarMotivo()
resources/views/admin/index.blade.php    — tabla de salidas actualizada
resources/views/livewire/dashboard-salidas.blade.php    — NUEVO
resources/views/components/⚡dashboard-salidas.blade.php — NUEVO (clase Livewire)
resources/views/livewire/correos-fallidos/correos-fallidos.php — dispatchSync()
```

## 📁 Archivos nuevos

```
resources/views/livewire/dashboard-salidas.blade.php
resources/views/components/⚡dashboard-salidas.blade.php
```

## ⚠️ Errores conocidos

### 419 (Page Expired)
- Causa: token CSRF de sesión expiró
- Solución: recargar página (Ctrl+Shift+R)
- No es bug, es comportamiento normal de sesión

### 7. Páginas de Error Personalizadas
- **Vistas:** `resources/views/errors/` con estilo AdminLTE
- **403** — "No tenés permisos" (icono candado, color warning)
- **404** — "Página no encontrada" (icono lupa, color danger)
- **500** — "Error interno del servidor" (icono servidor, color primary)
- **419** — "Sesión expirada" (icono reloj, color info)
- Todas con botón "Volver al Inicio" + "Volver"
- Responsive para mobile

### 8. Auditoría de Seguridad

**Resultados del escaneo:**

| Chequeo | Estado | Notas |
|---------|--------|-------|
| `composer audit` | ✅ OK | Sin vulnerabilidades conocidas |
| SQL injection | ✅ OK | Solo Eloquent, sin queries raw |
| XSS | ✅ OK | Solo vendor/AdminLTE usa `!!` |
| .env accesible | ✅ OK | No expuesto desde web |
| Uploads mime | ✅ OK | Whitelist: pdf, jpg, png, gif, doc, xls, ppt, txt |
| Throttle login | ✅ OK | 60 intentos/min (Fortify) |
| Session http_only | ✅ OK | Configurado |
| CSRF | ✅ OK | Laravel built-in |
| RedirectVisitante | ✅ OK | Bloquea rol 'visitante' del admin |
| DB prohibitive cmds | ✅ OK | `prohibitDestructiveCommands` en prod |
| Password policy | ✅ OK | 12 chars + symbols en prod |
| `eval/exec/shell_exec` | ✅ OK | Ninguno encontrado |
| Hardcoded creds | ✅ OK | Ninguno encontrado |

**Mejoras implementadas:**

1. **BackupController** — Agregada verificación `isAdmin()` en constructor
2. **Gates para backup** — `viewAny-backup`, `create-backup`, `delete-backup` en AppServiceProvider
3. **AdjuntoController** — Agregada verificación `esMiembro()` para prevenir IDOR
4. **SecurityHeaders middleware** — Nuevas cabeceras en todas las respuestas:
   - `X-XSS-Protection: 1; mode=block`
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: SAMEORIGIN`
   - `Referrer-Policy: strict-origin-when-cross-origin`
   - `Content-Security-Policy: frame-ancestors 'self'`
   - `Strict-Transport-Security` en producción

**Notas pendientes:**
- ⚠️ Ruta `/guardias-publicas/{guardia}/pdf-preview` es pública (intencional, solo guardias cerradas)
- ⚠️ Considerar agregar CSP más restrictivo en producción

### 9. Backup Diario Automático
- **Archivo:** `backup-diario.bat` — script para Windows Task Scheduler
- **Comando:** `php artisan backup:run --only-db`
- **Log:** `storage/logs/backup-scheduler.log` con éxito/error
- **Frecuencia recomendada:** Diario a las 2:00 AM

### 10. Dashboard con Gráficos Chart.js
- **Componente:** `app/Livewire/DashboardCharts.php`
- **Vista:** `resources/views/livewire/dashboard-charts.blade.php`
- **4 Gráficos interactivos:**
  1. **Actividad Semanal** (líneas) — Salidas vs Vuelos últimos 7 días
  2. **Estado de Conductores** (dona) — Activos vs Inactivos
  3. **Vehículos en Ruta** (barras) — Hoy: en ruta vs finalizados
  4. **Novedades por Tipo** (barras horizontales) — Este mes
- **Poll:** Actualización automática cada 30 segundos
- **Configuración:** Chart.js habilitado en `config/adminlte.php` (v4.4.1 CDN)
- **Integrado:** Agregado en `resources/views/admin/index.blade.php`

### 📋 Pendientes / Próximos pasos

### Opción A: Tarea programada para backups automáticos
- Configurar Task Scheduler en Windows para ejecutar `backup:run` diario
- Script batch + tarea programada (cada noche a las 2am)

### Opción B: Mejorar el dashboard
- Agregar gráficos con Chart.js (salidas, novedades, guardias)
- Mejorar visualización de alertas de conductores
- Timeline de novedades en tiempo real

### Opción C: Mejorar la landing pública
- Componente Livewire para mostrar novedades cerradas
- Filtros por fecha/guardia
- Búsqueda en novedades públicas

### Opción D: Mejoras generales
- Logs de actividad más detallados
- Notificaciones push / badge en navbar
- Export a Excel/PDF de reportes
- Mejorar la página de correos fallidos (más info, reintentos individuales)

### Opción E: Hardening adicional
- CSP más restrictivo en producción
- Rate limiting en rutas sensibles
- Logging de intentos de acceso no autorizado
- 2FA obligatorio para admins

### 7. Boleta de Cierre (para salidas que cruzan días)
- **Problema resuelto:** Cuando un vehículo sale un día y vuelve otro, la salida quedaba atada a la guardia del día de salida y no se podían calcular kms_recorridos/litros.
- **Solución:** Nueva tabla `boletas_cierre` independiente que se llena cuando el conductor entrega el vehículo.
- **Estructura:** `salida_id`, `fecha_entra`, `hora_entra`, `kms_entra`, `observaciones`
- **Modelo `BoletaCierre`:** Al guardar, actualiza automáticamente `kms_recorridos` y `litros` de la salida original.
- **Modelo `SalidaVehiculo`:** Nueva relación `hasOne(BoletaCierre::class)`, attributes `tiene_boleta` y `estado`.
- **Livewire `salidas-vehiculo`:** Nuevo modal "Boleta de Cierre" con campos: fecha/hora regreso, km al regreso, observaciones. Preview del cálculo automático.
- **Tabla de salidas:** Nueva columna "Estado" (⚠️ Pendiente / ✅ Cerrada) + botones de acción.
- **PDF `novedades.blade.php`:** Muestra fecha/hora completa de regreso, estado de la salida, observaciones de la boleta.

## 🔧 Comandos útiles para hoy

```bash
# Limpiar caché (si algo no funciona)
php artisan view:clear
php artisan optimize:clear

# Verificar rutas Livewire
php artisan route:list | grep livewire

# Verificar si hay errores en logs
tail -f storage/logs/laravel.log
```
