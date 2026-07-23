<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
    <p>Estimado/a,</p>

    <p>
        Se adjunta un archivo ZIP con las novedades de la guardia del
        <strong>{{ $guardia->date->format('d/m/Y') }}</strong>
        en formato PDF, junto con todos los documentos recibidos adjuntos a la guardia (sin embeber en el PDF).
    </p>

    <table style="margin: 16px 0; border-collapse: collapse;">
        <tr>
            <td style="padding: 2px 8px 2px 0; color: #666;">Capitán de Servicio:</td>
            <td><strong>{{ $guardia->capitan->grade }} {{ $guardia->capitan->name }} {{ $guardia->capitan->last_name }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 2px 8px 2px 0; color: #666;">Oficial de Día:</td>
            <td><strong>{{ $guardia->oficial->grade }} {{ $guardia->oficial->name }} {{ $guardia->oficial->last_name }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 2px 8px 2px 0; color: #666;">Estado:</td>
            <td><strong>{{ $guardia->status === 'open' ? 'Abierta' : 'Cerrada' }}</strong></td>
        </tr>
    </table>

    <p style="background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 10px 14px; border-radius: 4px;">
        📎 <strong>Adjunto:</strong> {{ $nombreArchivo }}
    </p>

    <p style="color: #666; font-size: 13px;">
        Enviado por {{ $remitenteName }} desde SIS-Novedades.
    </p>
</body>
</html>