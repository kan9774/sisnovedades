@echo off
REM ============================================
REM Backup Diario Automatico - SIS-Novedades
REM Ejecutar via Task Scheduler (diario 2:00 AM)
REM ============================================

cd /d "C:\inetpub\sisnovedades"

REM Ejecutar backup de base de datos
C:\php8.4\php.exe artisan backup:run --only-db

REM Verificar si fue exitoso (exit code 0)
if %errorlevel% equ 0 (
    echo [%date% %time%] Backup completado exitosamente >> "C:\inetpub\sisnovedades\storage\logs\backup-scheduler.log"
) else (
    echo [%date% %time%] ERROR: Backup fallido (exit code %errorlevel%) >> "C:\inetpub\sisnovedades\storage\logs\backup-scheduler.log"
)