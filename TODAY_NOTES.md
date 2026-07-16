# Notas del día — Livewire + Dashboard

## ✅ Lo que hicimos hoy

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

## 📋 Para mañana

### Prioridad 0: Sistema de Backup con Panel Livewire
- **Motor:** `spatie/laravel-backup` (CLI, sin UI oficial)
- **Interfaz:** Crear componente Livewire `BackupManager` (panel propio)
- **Funcionalidades del panel:**
  - Botón "Crear Backup Ahora" (ejecuta `backup:run`)
  - Tabla con lista de backups (fecha, tamaño, tipo)
  - Botón "Eliminar" en cada backup
  - Barra de progreso / estado (completado, en curso)
- **Rotación:** Configurar `config/backup.php` para borrar los viejos automáticamente (ej: mantener 7 días)
- **Instalación:**
  ```bash
  composer require spatie/laravel-backup
  php artisan vendor:publish --provider "Spatie\Backup\BackupServiceProvider"
  ```
- **Automatización:**
  - Configurar Tarea Programada (Task Scheduler en Windows) para `php artisan backup:run` diario

### Prioridad 1: Personalizar páginas de error
- Crear `resources/views/errors/` con vistas personalizadas
- Estilo AdminLTE (consistente con el sistema)
- Iconos FontAwesome
- Botón "Volver al inicio"

**Errores a personalizar (orden de prioridad):**
1. **403** — "No tenés permisos" (más común en el sistema)
2. **404** — "Página no encontrada"
3. **500** — "Error interno del servidor"
4. **419** — "Sesión expiró" (ya redirige, pero dejar página bonita)

**Diseño propuesto:**
- Fondo claro AdminLTE
- Icono grande FontAwesome centrado
- Título del error
- Descripción breve en español
- Botón "Volver al inicio" con enlace a `/admin`
- Logo del sistema arriba

### Prioridad 2: Auditoría de Seguridad
- **Paquetes vulnerables:** Ejecutar `composer audit` para detectar dependencias con fallos de seguridad conocidos.
- **Middleware activo:** Revisar `bootstrap/app.php` (y `app/Http/Kernel.php` si existe):
  - `VerifyCsrfToken`: Protege contra ataques CSRF (obligatorio).
  - `throttle`: Rate limiting (evita fuerza bruta en login/API).
  - `RedirectVisitante`: Evita acceso a invitados.
  - `EnsureEmailIsVerifiedIfEnabled`: Verifica emails.
- **Protección XSS:** Verificar que Blade use `{{ }}` (automático) y no `!!` (peligroso).
- **SQL Injection:** Verificar que no existan consultas raw (`DB::select`, etc.) y se use Eloquent.
- **Livewire Security:** Revisar `config/livewire.php` (max upload size, etc.).
- **Archivos sensibles:** Verificar que `.env` y `storage` no sean accesibles desde el navegador.
- **CORS:** Verificar si hay rutas API expuestas sin protección.

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
