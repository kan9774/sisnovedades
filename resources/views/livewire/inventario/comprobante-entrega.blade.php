<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante #{{ $entrega->id }} — {{ $entrega->esEntrega() ? 'Entrega' : 'Devolución' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1a1a1a;
            margin: 0;
            padding: 2rem;
        }
        .comprobante {
            max-width: 800px;
            margin: 0 auto;
        }
        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #0B2545;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .encabezado h1 {
            font-size: 1.4rem;
            margin: 0 0 .25rem;
            color: #0B2545;
        }
        .encabezado .subtitulo {
            font-size: .85rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .encabezado .numero {
            text-align: right;
            font-size: .9rem;
            color: #555;
        }
        .datos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem 2rem;
            margin-bottom: 1.5rem;
            font-size: .92rem;
        }
        .datos dt {
            font-weight: 700;
            color: #333;
        }
        .datos dd {
            margin: 0 0 .5rem;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            font-size: .92rem;
        }
        th, td {
            border: 1px solid #ccc;
            padding: .5rem .6rem;
            text-align: left;
        }
        th {
            background: #0B2545;
            color: #fff;
        }
        td.cantidad {
            text-align: center;
            width: 90px;
        }
        .motivo {
            margin-bottom: 2.5rem;
            font-size: .92rem;
        }
        .firmas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 3rem;
        }
        .firma {
            text-align: center;
        }
        .firma .linea {
            border-top: 1px solid #333;
            margin-bottom: .4rem;
            padding-top: .4rem;
        }
        .firma small {
            color: #666;
        }
        .btn-imprimir {
            display: inline-block;
            margin-bottom: 1.5rem;
            padding: .5rem 1rem;
            background: #0B2545;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: .9rem;
        }
        @media print {
            .btn-imprimir { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="comprobante">
        <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir</button>

        <div class="encabezado">
            <div>
                <div class="subtitulo">BCOM1 · Inventario</div>
                <h1>Comprobante de {{ $entrega->esEntrega() ? 'Entrega' : 'Devolución' }}</h1>
            </div>
            <div class="numero">
                Nº {{ str_pad($entrega->id, 6, '0', STR_PAD_LEFT) }}<br>
                {{ $entrega->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <dl class="datos">
            <div>
                <dt>Origen</dt>
                <dd>{{ $entrega->ubicacionOrigen->nombre }}</dd>
            </div>
            <div>
                <dt>Destino</dt>
                <dd>{{ $entrega->ubicacionDestino->nombre }}</dd>
            </div>
            <div>
                <dt>Gestionado por</dt>
                <dd>{{ $entrega->usuario->name }}</dd>
            </div>
            <div>
                <dt>Cantidad de ítems</dt>
                <dd>{{ $entrega->movimientos->count() }}</dd>
            </div>
        </dl>

        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th>Detalle</th>
                    <th class="cantidad">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entrega->movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->item->nombre }}</td>
                        <td>
                            @if ($movimiento->itemUnidad)
                                Nº de serie: {{ $movimiento->itemUnidad->numero_serie ?? "unidad #{$movimiento->itemUnidad->id}" }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="cantidad">
                            {{ $movimiento->itemUnidad ? '1 unidad' : $movimiento->cantidad }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($entrega->motivo)
            <div class="motivo">
                <strong>Motivo / observaciones:</strong><br>
                {{ $entrega->motivo }}
            </div>
        @endif

        <div class="firmas">
            <div class="firma">
                <div class="linea">Entregado por</div>
                <small>Aclaración y firma</small>
            </div>
            <div class="firma">
                <div class="linea">Recibido por</div>
                <small>Aclaración y firma</small>
            </div>
        </div>
    </div>
</body>
</html>