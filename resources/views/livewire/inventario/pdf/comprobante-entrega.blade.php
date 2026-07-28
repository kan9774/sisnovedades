<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .subtitulo { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .datos-generales { margin-bottom: 20px; }
        .datos-generales td { border: none; padding: 3px 8px 3px 0; }
        .datos-generales td:first-child { font-weight: bold; width: 140px; }
        .firma { margin-top: 60px; }
        .firma-linea { border-top: 1px solid #222; width: 250px; margin-top: 40px; padding-top: 4px; text-align: center; }
    </style>
</head>
<body>
    <h1>Comprobante de {{ $entrega->esDevolucion() ? 'Devolución' : 'Entrega' }}</h1>
    <p class="subtitulo">N° {{ $entrega->id }} — {{ $entrega->created_at->format('d/m/Y H:i') }}</p>

    <table class="datos-generales">
        <tr>
            <td>Tipo:</td>
            <td>{{ $entrega->esDevolucion() ? 'Devolución' : 'Entrega' }}</td>
        </tr>
        <tr>
            <td>Ubicación origen:</td>
            <td>{{ $entrega->ubicacionOrigen->nombre ?? '—' }}</td>
        </tr>
        <tr>
            <td>Ubicación destino:</td>
            <td>{{ $entrega->ubicacionDestino->nombre ?? '—' }}</td>
        </tr>
        <tr>
            <td>Responsable:</td>
            <td>{{ $entrega->usuario->name ?? '—' }}</td>
        </tr>
        @if ($entrega->motivo)
            <tr>
                <td>Motivo:</td>
                <td>{{ $entrega->motivo }}</td>
            </tr>
        @endif
    </table>

    <table>
        <thead>
            <tr>
                <th>Ítem</th>
                <th>N° Serie</th>
                <th style="text-align: right;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entrega->movimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento->item->codigo ?? '—' }} — {{ $movimiento->item->nombre ?? '—' }}</td>
                    <td>{{ $movimiento->itemUnidad->numero_serie ?? '—' }}</td>
                    <td style="text-align: right;">{{ $movimiento->cantidad ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #888;">Sin ítems registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="firma">
        <div class="firma-linea">Firma del responsable</div>
    </div>
</body>
</html>