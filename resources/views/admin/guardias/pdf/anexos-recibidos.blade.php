{{--
    Sección de Anexos: todo lo "Recibido" con adjuntos, para agregar al final
    del PDF de la guardia. Las imágenes se embeben acá mismo; los PDFs
    adjuntos se listan con una nota y se fusionan como páginas extra
    en RecibidosPdfGenerator (fuera de este Blade, con FPDI).

    Espera: $guardia (con novedades.adjuntos, novedades.organismo,
    novedades.oficina, novedades.tomadoPor cargados)
--}}

@php
    $recibidosConAdjuntos = $guardia->novedades
        ->where('direction', 'Recibido')
        ->filter(fn($n) => $n->adjuntos->isNotEmpty())
        ->values();
@endphp

@if ($recibidosConAdjuntos->isNotEmpty())
    <div style="page-break-before: always;"></div>

    <div class="header text-left">
        <h3>Anexos - Documentación Recibida</h3>
    </div>

    @foreach ($recibidosConAdjuntos as $novedad)
        <div class="seccion" style="page-break-inside: avoid; margin-bottom: 18px; border: 1px solid #000; padding: 6px;">
            {{-- Encabezado del anexo --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 5px; font-size: 10px; width: 25%;">
                        <strong>Origen:</strong><br>
                        {{ $novedad->organismo->name ?? 'Sin especificar' }}
                    </td>
                    <td style="border: 1px solid #000; padding: 3px 5px; font-size: 10px; width: 25%;">
                        <strong>Fecha y hora de recibido:</strong><br>
                        {{ $novedad->tomado_en ? $novedad->tomado_en->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td style="border: 1px solid #000; padding: 3px 5px; font-size: 10px; width: 25%;">
                        <strong>Oficina:</strong><br>
                        {{ $novedad->oficina->nombre ?? 'Sin asignar' }}
                    </td>
                    <td style="border: 1px solid #000; padding: 3px 5px; font-size: 10px; width: 25%;">
                        <strong>Tomado por:</strong><br>
                        @if ($novedad->tomadoPor)
                            {{ strtoupper($novedad->tomadoPor->grade) }} {{ $novedad->tomadoPor->name }} {{ $novedad->tomadoPor->last_name }}
                            @if ($novedad->tomado_en)
                                <br><small>{{ $novedad->tomado_en->format('H:i') }} hs.</small>
                            @endif
                        @else
                            S/D
                        @endif
                    </td>
                </tr>
            </table>

            <p style="font-size: 10px; margin-bottom: 6px;"><strong>Texto:</strong> {{ $novedad->text }}</p>

            {{-- Adjuntos --}}
            @foreach ($novedad->adjuntos as $adjunto)
                <div style="margin-bottom: 8px; text-align: center;">
                    @if ($adjunto->esImagen())
                        @php
                            $rutaCompleta = \Illuminate\Support\Facades\Storage::disk('guardias')->path($adjunto->file_path);
                            $base64 = null;
                            if (file_exists($rutaCompleta)) {
                                $tipo = $adjunto->file_type;
                                $contenido = file_get_contents($rutaCompleta);
                                $base64 = 'data:' . $tipo . ';base64,' . base64_encode($contenido);
                            }
                        @endphp
                        @if ($base64)
                            <img src="{{ $base64 }}" style="max-width: 100%; max-height: 500px;">
                        @else
                            <p style="font-size: 10px; color: #900;">[No se pudo cargar la imagen: {{ $adjunto->file_name }}]</p>
                        @endif
                    @elseif ($adjunto->esPdf())
                        <p style="font-size: 10px; font-style: italic;">
                            📎 Ver anexo completo en las páginas siguientes: <strong>{{ $adjunto->file_name }}</strong>
                        </p>
                    @else
                        <p style="font-size: 10px; font-style: italic;">
                            📎 Archivo adjunto: <strong>{{ $adjunto->file_name }}</strong> ({{ $adjunto->tamanoLegible() }})
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
@endif