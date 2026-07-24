@echo off
cd /d "C:\inetpub\sisnovedades"

C:\php8.4\php.exe artisan backup:run --only-db >> "C:\inetpub\sisnovedades\storage\logs\backup-scheduler.log" 2>&1

if %errorlevel% equ 0 (
    echo [%date% %time%] Backup completado exitosamente >> "C:\inetpub\sisnovedades\storage\logs\backup-scheduler.log"
) else (
    echo [%date% %time%] ERROR: Backup fallido (exit code %errorlevel%) >> "C:\inetpub\sisnovedades\storage\logs\backup-scheduler.log"
)