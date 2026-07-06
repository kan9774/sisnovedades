<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; background: #f4f6f8; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden;">
        <div style="background: #0a1e32; padding: 20px; text-align: center;">
            <h2 style="color: #fff; margin: 0;">Nuevo mensaje de contacto</h2>
        </div>
        <div style="padding: 25px;">
            <p><strong>Nombre:</strong> {{ $nombreContacto }}</p>
            <p><strong>Email:</strong> {{ $emailContacto }}</p>
            <p><strong>Mensaje:</strong></p>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 3px solid #0d6efd;">
                {{ $mensajeContacto }}
            </div>
        </div>
    </div>
</body>
</html>