@echo off
cd /d C:\laragon\www\novedades
C:\laragon\bin\php\php-8.3.30\php.exe artisan queue:work --stop-when-empty --max-time=50 --tries=3 --backoff=10
