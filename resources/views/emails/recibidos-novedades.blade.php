<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #333;
            font-size: 20px;
            margin: 0;
        }
        .header h2 {
            color: #666;
            font-size: 14px;
            margin: 5px 0 0 0;
            font-weight: normal;
        }
        .content {
            padding: 15px 0;
        }
        .content p {
            margin: 10px 0;
        }
        .info {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #333;
            margin: 15px 0;
        }
        .info strong {
            color: #333;
        }
        .info span {
            display: block;
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .attachment-notice {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .attachment-notice i {
            color: #007bff;
            font-size: 20px;
            margin-right: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <h2>Sistema de Novedades</h2>
        </div>

        {{-- Content --}}
        <div class="content">
            <p>Estimado Capitán de Servicio,</p>

            <p>Se adjunta a este correo el archivo PDF con la relación de todos los archivos recibidos correspondientes a la guardia del <strong>{{ $guardia->date->format('d/m/Y') }}</strong>.</p>

            <div class="info">
                <span><strong>Guardia:</strong> {{ config('organizacion.nombre') }}</span>
                <span><strong>Fecha:</strong> {{ $guardia->date->format('d/m/Y') }}</span>
                <span><strong>Estado:</strong> 
                    @if($guardia->status === 'open')
                        <span style="color: green;">Abierta</span>
                    @else
                        <span style="color: #999;">Cerrada</span>
                    @endif
                </span>
                <span><strong>Oficial de Día:</strong> {{ $guardia->oficial->grade }} {{ $guardia->oficial->name }} {{ $guardia->oficial->last_name }}</span>
            </div>

            <div class="attachment-notice">
                <i>📎</i>
                <strong>Adjunto:</strong> {{ $nombreArchivo }}
                <br>
                <span style="font-size: 12px; color: #666;">Este archivo contiene todos los documentos recibidos con sus imágenes incrustadas.</span>
            </div>

            <p>Saludos cordiales,</p>
            <p><strong>{{ $nombreRemitente }}</strong><br>
            Sistema de Novedades</p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>Este es un correo automático del sistema de novedades.</p>
            <p>{{ config('app.name') }} - {{ config('app.url') }}</p>
        </div>
    </div>
</body>
</html>