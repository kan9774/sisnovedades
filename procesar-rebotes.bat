@echo off
cd /d "C:\laragon\www\novedades"
php artisan mail:procesar-rebotes
echo Proceso de rebotes completado en %date% %time%
exit