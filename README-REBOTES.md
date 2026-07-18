# Sistema de manejo de rebotes de correo

## Implementación propuesta

Este sistema implementa el manejo de mensajes de rebote (bounces) para capturar errores en el envío de correos.

## Estructura de datos

1. **Tabla `guardia_correos_enviados`**: Almacena los envíos con Message-ID para correlación
2. **Tabla `guardia_correos_fallidos`**: Almacena errores detectados (ya existente)

## Comando Artisan

- `php artisan mail:procesar-rebotes`: Procesa mensajes de rebote de la bandeja de entrada

## Implementación técnica

El proceso funciona así:
1. El comando se ejecuta periódicamente (cada 15-30 minutos)
2. Se conecta al servidor IMAP de Gmail
3. Busca mensajes no leídos de mailer-daemon o postmaster
4. Identifica los mensajes DSN (Delivery Status Notifications)
5. Extrae información del destinatario y motivo del error
6. Correlaciona con envíos previos usando Message-ID
7. Registra errores en la tabla `guardia_correos_fallidos`

## Limitaciones

- Con cuenta Gmail normal, el Return-Path siempre es tu dirección
- Requiere acceso IMAP habilitado en la cuenta de Gmail
- El proceso de parseo del cuerpo DSN es complejo y requiere librerías adicionales