# Contexto del Proyecto

## Historial de Cambios

### 2026-08-14 — Soporte multi-motor de base de datos (MySQL/PostgreSQL) en spatie/laravel-backup

- **config/database.php**: agregado bloque `dump.dump_binary_path` a la conexión `pgsql`, parametrizado vía `PG_DUMP_BINARY_PATH` en `.env`
- **config/backup.php**: naming de backups ahora incluye el driver activo (ej. `novedades-mysql-*.zip` / `novedades-pgsql-*.zip`); clave `database_dump_binary_path` documentada como dead code (no es leída por el paquete, ver comentario en el archivo)
- **.env.example**: agregadas `MYSQL_DUMP_BINARY_PATH` y `PG_DUMP_BINARY_PATH`
- **resources/views/livewire/backup-manager.blade.php**: texto de UI ahora dinámico según `config('database.default')`
- Verificado con backup real: dump exitoso contra `pgsql` local usando PostgreSQL 17 Command Line Tools (sin servidor local instalado)
