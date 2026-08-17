# Contexto del Proyecto

## Historial de Cambios

### 2026-08-14 — Soporte multi-motor de base de datos (MySQL/PostgreSQL) en spatie/laravel-backup

- **config/database.php**: agregado bloque `dump.dump_binary_path` a la conexión `pgsql`, parametrizado vía `PG_DUMP_BINARY_PATH` en `.env`
- **config/backup.php**: naming de backups ahora incluye el driver activo (ej. `novedades-mysql-*.zip` / `novedades-pgsql-*.zip`); clave `database_dump_binary_path` documentada como dead code (no es leída por el paquete, ver comentario en el archivo)
- **.env.example**: agregadas `MYSQL_DUMP_BINARY_PATH` y `PG_DUMP_BINARY_PATH`
- **resources/views/livewire/backup-manager.blade.php**: texto de UI ahora dinámico según `config('database.default')`
- Verificado con backup real: dump exitoso contra `pgsql` local usando PostgreSQL 17 Command Line Tools (sin servidor local instalado)

### 2026-08-14 — Sistema de restauración de backups desde la UI

- **app/Jobs/RestoreDatabaseJob.php**: Job en cola con flujo completo de restore
  - Backup de seguridad automático pre-restore (`pre-restore-safety-{timestamp}.zip`)
  - Si el safety backup falla, se aborta el restore (nunca restaurar sin resguardo)
  - `artisan down` → extraer zip → localizar `.sql` en `db-dumps/` → importar → `artisan up`
  - MySQL: `mysql -u{user} -p{pass} {database} < file.sql`
  - PostgreSQL: `psql -U{user} -h{host} -d{database} -f file.sql` con `PGPASSWORD` como env var
  - Logging dedicado en `storage/logs/restore-{id}.log`
  - Siempre ejecuta `artisan up` en finally, incluso si el restore falla
- **app/Livewire/BackupManager.php**: propiedades de estado (`idle/backing_up_safety/restoring/completed/failed`),
  método `openRestore()` con validación de motor, `startRestore()` despachando el Job,
  `getRestoreStatus()` consultando cache para polling
- **resources/views/livewire/backup-manager.blade.php**:
  - Modal de advertencia (Bootstrap) antes de restaurar
  - Modal ops-panel con x-teleport="body": info del backup, detección de motor,
    validación de compatibilidad (motor backup vs motor activo),
    input de texto para confirmación (case-sensitive, sin extensión .zip)
  - wire:poll.5s para consultar estado del Job en cache
  - Spinner + texto de estado durante el proceso
  - Mensaje de éxito/error al terminar, con path del safety backup en caso de error
- **app/Providers/AppServiceProvider.php**: gate `restore-backup` (solo admins)
- **Patrón de modales**: ops-panel con x-teleport="body", `is-open` class (no x-show)
- **Permisos**: visible solo si `$user->isAdmin()` (gate + check en backend del Job)

### 2026-08-16 — Vuelos migrado de Blade → Livewire

- **app/Livewire/Vuelos.php** (476 líneas) — CRUD completo: index, crear, editar, eliminar
  - Selección de palomas participantes con checkbox
  - Anilla de competición por paloma
  - Lógica de estados: palomas se marcan "En competición"/"En vareo" al crear vuelo
  - Edición restringida cuando vuelo está finalizado
  - Eliminación solo si no tiene palomas registradas
- **app/Livewire/Vuelos/VuelosResultados.php** (151 líneas) — Cargar resultados de vuelo
  - Formulario por paloma: distancia_km, hora_llegada, posicion, observaciones
  - Cálculo automático de tiempo_vuelo y velocidad_media
  - Finaliza vuelo y devuelve palomas a estado anterior
  - Historial de cambios de estado registrado
- **routes/web.php**: `VueloController` eliminado, rutas apuntan a Livewire
- **Files eliminados**: `app/Http/Controllers/VueloController.php`, `resources/views/admin/palomar/vuelos/*.blade.php`
