@echo off
REM ============================================
REM Backup Diario Automatico - Novedades
REM Ejecutar via Task Scheduler (diario 2:00 AM)
REM ============================================

cd /d "C:\laragon\www\novedades"

REM Ejecutar backup de base de datos
php artisan backup:run --only-db

REM Verificar si fue exitoso (exit code 0)
if %errorlevel% equ 0 (
    echo [%date% %time%] Backup completado exitosamente >> "C:\laragon\www\novedades\storage\logs\backup-scheduler.log"
) else (
    echo [%date% %time%] ERROR: Backup fallido (exit code %errorlevel%) >> "C:\laragon\www\novedades\storage\logs\backup-scheduler.log"
)
