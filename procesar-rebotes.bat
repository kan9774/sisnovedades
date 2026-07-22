@echo off
cd /d "C:\inetpub\sisnovedades"
C:\php8.4\php.exe artisan mail:procesar-rebotes
echo Proceso de rebotes completado en %date% %time%
exit