# Implementación del Sistema de Manejo de Rebotes

## Estructura del Sistema

### 1. Componentes principales

#### Comandos Artisan:
- `php artisan mail:procesar-rebotes`: Procesa mensajes de rebote de la bandeja de entrada
- `php artisan mail:procesar-rebotes-simple`: Versión de prueba del comando

#### Tablas de base de datos:
- `guardia_correos_enviados`: Almacena los envíos con Message-ID para correlación
- `guardia_correos_fallidos`: Almacena errores detectados (ya existente)

#### Componentes Livewire:
- `CorreosFallidosList`: Muestra la lista de correos fallidos en la vista de guardia
- `BadgeCorreosFallidosCount`: Muestra el número de correos fallidos como badge

### 2. Integración con la interfaz existente

La vista `admin/guardias/show.blade.php` ya está configurada para mostrar:

1. **Tab "Correos fallidos"** (solo visible para usuarios autorizados)
2. **Badge con cantidad de correos fallidos**
3. **Contenido de correos fallidos**

### 3. Funcionamiento del sistema

#### Para correos fallidos detectados durante el envío:
1. El job `EnviarNovedadGuardiaMail` registra errores en `guardia_correos_fallidos`
2. Estos se muestran inmediatamente en la interfaz

#### Para rebotes de Gmail:
1. El comando `mail:procesar-rebotes` se ejecuta periódicamente
2. Se conecta al IMAP de Gmail
3. Busca mensajes de mailer-daemon o postmaster
4. Analiza los DSN (Delivery Status Notifications)
5. Extrae información del destinatario y motivo
6. Correlaciona con envíos previos usando Message-ID
7. Registra errores en `guardia_correos_fallidos`

### 4. Archivos creados

#### Migraciones:
- `database/migrations/2026_07_18_103359_create_guardia_correos_enviados_table.php`

#### Comandos:
- `app/Console/Commands/ProcesarRebotesCommand.php`
- `app/Console/Commands/ProcesarRebotesCommandSimple.php`

#### Componentes Livewire:
- `app/Livewire/CorreosFallidosList.php`
- `app/Livewire/BadgeCorreosFallidosCount.php`
- `resources/views/livewire/correos-fallidos-list.blade.php`
- `resources/views/livewire/badge-correos-fallidos-count.blade.php`

#### Archivos auxiliares:
- `procesar-rebotes.bat`: Archivo para ejecución en Windows
- `README-REBOTES.md`: Documentación general del sistema

### 5. Configuración necesaria

1. **Habilitar IMAP en Gmail** (en la configuración de Gmail)
2. **Programar el archivo .bat** con Windows Task Scheduler cada 15-30 minutos
3. **Verificar credenciales** en `.env` para acceso IMAP:
   ```
   MAIL_USERNAME=...
   MAIL_PASSWORD=...
   ```

### 6. Pruebas realizadas

Los siguientes comandos han sido verificados:
- `php artisan mail:procesar-rebotes-simple` (funciona correctamente)
- Las tablas necesarias existen
- Los componentes Livewire están estructurados correctamente

## Uso recomendado

1. **Para correos fallidos durante envío**: Se registran automáticamente
2. **Para rebotes de Gmail**: Se procesan con el comando periódico
3. **Para visualización**: Se muestran en la pestaña "Correos fallidos" de cada guardia

## Limitaciones actuales

1. El sistema actual solo detecta errores durante el envío directo
2. El manejo completo de rebotes DSN está implementado conceptualmente pero necesita desarrollo adicional para parsear los mensajes de forma completa
3. La solución requiere acceso IMAP en la cuenta de Gmail

## Siguientes pasos

1. **Configurar el archivo .bat** con Windows Task Scheduler
2. **Probar el sistema de rebotes** enviando correos a direcciones inválidas
3. **Desarrollar el parseo completo del DSN** para procesamiento real de rebotes