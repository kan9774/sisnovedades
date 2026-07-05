<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parte Diario - {{ $palomar->nombre }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #222;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 4px 0;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #555;
        }
        .resumen {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .resumen td {
            border: 1px solid #ccc;
            padding: 6px 10px;
        }
        .resumen td.label {
            font-weight: bold;
            background-color: #f4f4f4;
            width: 60%;
        }
        table.listado {
            width: 100%;
            border-collapse: collapse;
        }
        table.listado th, table.listado td {
            border: 1px solid #ccc;
            padding: 5px 7px;
            text-align: left;
        }
        table.listado th {
            background-color: #333;
            color: #fff;
        }
        table.listado tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            color: #fff;
            font-size: 10px;
        }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #777;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Parte Diario - {{ $palomar->nombre }}</h1>
        <p>{{ $palomar->ubicacion ?? 'Sin ubicación registrada' }} &middot; Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        $total = $palomar->palomas->count();
        $adultas = $palomar->palomas->filter(fn($p) => !$p->es_pichon)->count();
        $pichones = $palomar->palomas->filter(fn($p) => $p->es_pichon)->count();
        $reproductoras = $palomar->palomas->filter(fn($p) => optional($p->estado)->nombre === 'Reproductora')->count();
        $ausentes = $palomar->palomas->filter(fn($p) => optional($p->estado)->nombre === 'Ausente')->count();
        $bajas = $palomar->palomas->filter(fn($p) => optional($p->estado)->nombre === 'Baja')->count();
    @endphp

    <table class="resumen">
        <tr><td class="label">Total existencias</td><td>{{ $total }}</td></tr>
        <tr><td class="label">Adultas</td><td>{{ $adultas }}</td></tr>
        <tr><td class="label">Pichones</td><td>{{ $pichones }}</td></tr>
        <tr><td class="label">Reproductoras</td><td>{{ $reproductoras }}</td></tr>
        <tr><td class="label">Ausentes</td><td>{{ $ausentes }}</td></tr>
        <tr><td class="label">Bajas</td><td>{{ $bajas }}</td></tr>
    </table>

    <table class="listado">
        <thead>
            <tr>
                <th>Anilla</th>
                <th>Nombre</th>
                <th>Sexo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($palomar->palomas as $paloma)
                <tr>
                    <td>{{ $paloma->anilla }}</td>
                    <td>{{ $paloma->nombre ?? '-' }}</td>
                    <td>{{ ucfirst($paloma->sexo) }}</td>
                    <td>
                        <span class="badge" style="background-color: {{ optional($paloma->estado)->color ?? '#6c757d' }};">
                            {{ optional($paloma->estado)->nombre ?? 'Sin estado' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No hay palomas registradas en este palomar.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistema de Palomar &middot; Página generada automáticamente
    </div>
</body>
</html>