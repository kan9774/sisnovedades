<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; background: #f4f6f8; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden;">
        <div style="background: #0a1e32; padding: 20px; text-align: center;">
            <h2 style="color: #fff; margin: 0;">Nueva sugerencia recibida</h2>
        </div>
        <div style="padding: 25px;">
            <p><strong>Nombre:</strong> {{ $data['nombre'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
            <p><strong>Rol:</strong> {{ $data['rol'] }}</p>
            <p><strong>Prioridad:</strong> {{ ucfirst($data['prioridad']) }}</p>
            <p><strong>Tipo:</strong> {{ ucfirst($data['tipo']) }}</p>
            <p><strong>Fecha:</strong> {{ $data['fecha'] }}</p>
            <p><strong>Descripción:</strong></p>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 3px solid #0d6efd;">
                {{ $data['mensaje'] }}
            </div>
            <p style="margin-top: 20px; color: #6c757d; font-size: 12px;">
                IP: {{ $data['ip'] }}<br>
                User agent: {{ $data['user_agent'] }}
            </p>
        </div>
    </div>
</body>
</html>