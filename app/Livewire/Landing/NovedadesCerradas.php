<?php

namespace App\Livewire\Landing;

use App\Models\Attach;
use App\Models\Guard;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class NovedadesCerradas extends Component
{
    use WithPagination;

    #[Url(as: 'buscar')]
    public string $search = '';

    public int $perPage = 12;

    // === VISTA (lista | calendario) ===
    public string $vista = 'calendario';

    #[Url(as: 'mes')]
    public ?int $mes = null;

    #[Url(as: 'anio')]
    public ?int $anio = null;

    // === PANEL DE GUARDIA ===
    public ?int $guardiaId = null;
    public bool $showPanel = false;
    public string $panelTab = 'pdf'; // 'pdf' | 'recibidos' | 'expedidos'

    // === MODAL DE ADJUNTO INDIVIDUAL ===
    public bool $showAdjunto = false;
    public array $adjuntoData = [];

    protected $paginationTheme = 'bootstrap';

    const MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    public function mount(): void
    {
        $this->mes ??= (int) now()->month;
        $this->anio ??= (int) now()->year;
    }

    // ---------------------------------------------------------------
    // LISTADO DE GUARDIAS CERRADAS (vista lista, con búsqueda)
    // ---------------------------------------------------------------
    #[Computed]
    public function guardiasCerradas()
    {
        return Guard::query()
            ->where('status', 'closed')
            ->when($this->search, function ($q) {
                $q->whereHas('novedades', function ($n) {
                    $n->where('affair', 'like', "%{$this->search}%")
                        ->orWhere('text', 'like', "%{$this->search}%")
                        ->orWhere('number', 'like', "%{$this->search}%");
                });
            })
            ->withCount([
                'novedades',
                'novedadesPersonal',
                'novedadesRancho',
                'salidasVehiculos',
            ])
            ->orderByDesc('date')
            ->paginate($this->perPage);
    }

    // ---------------------------------------------------------------
    // CALENDARIO — GUARDIAS CERRADAS DEL MES SELECCIONADO
    // ---------------------------------------------------------------
    #[Computed]
    public function guardiasDelMes()
    {
        return Guard::query()
            ->where('status', 'closed')
            ->whereYear('date', $this->anio)
            ->whereMonth('date', $this->mes)
            ->get()
            ->keyBy(fn($g) => $g->date->day);
    }

    #[Computed]
    public function diasCalendario(): array
    {
        $primerDia = \Carbon\Carbon::create($this->anio, $this->mes, 1);
        $offset = $primerDia->dayOfWeekIso - 1; // 0 = Lunes

        $dias = array_fill(0, $offset, null);

        for ($d = 1; $d <= $primerDia->daysInMonth; $d++) {
            $dias[] = $d;
        }

        return $dias;
    }

    public function nombreMesActual(): string
    {
        return self::MESES[$this->mes] . ' ' . $this->anio;
    }

    public function esHoy(int $dia): bool
    {
        return $dia === now()->day && $this->mes === now()->month && $this->anio === now()->year;
    }

    // ---------------------------------------------------------------
    // GUARDIA SELECCIONADA (para el panel)
    // ---------------------------------------------------------------
    #[Computed]
    public function guardiaSeleccionada()
    {
        if (!$this->guardiaId) {
            return null;
        }

        return Guard::query()
            ->where('status', 'closed')
            ->with([
                'capitan',
                'oficial',
                'escribiente',
                'novedades.organismo',
                'novedades.adjuntos',
                'novedadesPersonal',
                'novedadesRancho.unidad',
                'ranchoMenu',
                'salidasVehiculos.vehiculo',
                'salidasVehiculos.conductor',
            ])
            ->findOrFail($this->guardiaId);
    }

    // ---------------------------------------------------------------
    // ADJUNTOS POR CARPETA (Recibidos / Expedidos)
    // Agrupados por tipo de novedad (Radio, Fax, Email)
    // ---------------------------------------------------------------
    #[Computed]
    public function adjuntosRecibidos()
    {
        $guardia = $this->guardiaSeleccionada;
        if (!$guardia) {
            return [];
        }

        $recibidos = $guardia->novedades->where('direction', 'Recibido');
        $result = [];
        $tipos = ['Radio', 'Fax', 'Correo Electrónico'];

        foreach ($tipos as $tipo) {
            $items = $recibidos->where('type', $tipo);
            $adjuntos = $items->flatMap(fn($n) => $n->adjuntos);

            if ($adjuntos->count() > 0) {
                $result[$tipo] = $adjuntos;
            }
        }

        return $result;
    }

    #[Computed]
    public function adjuntosExpedidos()
    {
        $guardia = $this->guardiaSeleccionada;
        if (!$guardia) {
            return [];
        }

        $expedidos = $guardia->novedades->where('direction', 'Expedido');
        $result = [];
        $tipos = ['Radio', 'Fax', 'Correo Electrónico'];

        foreach ($tipos as $tipo) {
            $items = $expedidos->where('type', $tipo);
            $adjuntos = $items->flatMap(fn($n) => $n->adjuntos);

            if ($adjuntos->count() > 0) {
                $result[$tipo] = $adjuntos;
            }
        }

        return $result;
    }

    // ---------------------------------------------------------------
    // ACCIONES
    // ---------------------------------------------------------------
    public function verGuardia(int $guardiaId): void
    {
        $this->guardiaId = $guardiaId;
        $this->showPanel = true;
        $this->panelTab = 'pdf';
        $this->resetPage();
    }

    public function cerrarPanel(): void
    {
        $this->showPanel = false;
        $this->guardiaId = null;
        $this->panelTab = 'pdf';
    }

    public function cambiarTab(string $tab): void
    {
        $this->panelTab = $tab;
    }

    public function abrirAdjunto(int $adjuntoId): void
    {
        $guardia = $this->guardiaSeleccionada;
        if (!$guardia) {
            return;
        }

        $adjunto = Attach::where('id', $adjuntoId)
            ->whereRelation('novedad.guardia', 'id', $guardia->id)
            ->firstOrFail();

        $this->adjuntoData = [
            'id' => $adjunto->id,
            'name' => $adjunto->file_name,
            'url' => $adjunto->url(),
            'type' => $adjunto->file_type,
            'size' => $adjunto->file_size,
            'novedad_number' => $adjunto->novedad->number,
            'novedad_direction' => $adjunto->novedad->direction,
            'is_image' => $adjunto->esImagen(),
            'is_pdf' => $adjunto->esPdf(),
        ];

        $this->showAdjunto = true;
    }

    public function cerrarAdjunto(): void
    {
        $this->showAdjunto = false;
        $this->adjuntoData = [];
    }

    // ---------------------------------------------------------------
    // VISTA Y NAVEGACIÓN DE CALENDARIO
    // ---------------------------------------------------------------
    public function cambiarVista(string $vista): void
    {
        $this->vista = $vista;
    }

    public function mesAnterior(): void
    {
        $fecha = \Carbon\Carbon::create($this->anio, $this->mes, 1)->subMonthNoOverflow();
        $this->mes = $fecha->month;
        $this->anio = $fecha->year;
    }

    public function mesSiguiente(): void
    {
        $fecha = \Carbon\Carbon::create($this->anio, $this->mes, 1)->addMonthNoOverflow();
        $this->mes = $fecha->month;
        $this->anio = $fecha->year;
    }

    public function irAHoy(): void
    {
        $this->mes = (int) now()->month;
        $this->anio = (int) now()->year;
    }

    // ---------------------------------------------------------------
    // BÚSQUEDA
    // ---------------------------------------------------------------
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------
    public function clasificacionBadge(string $clas): string
    {
        return match ($clas) {
            'Urgente' => 'bg-danger',
            'Destello' => 'bg-warning text-dark',
            'Prioritario' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    public function tipoIcon(string $type): string
    {
        return match ($type) {
            'Radio' => 'fa-satellite-dish',
            'Fax' => 'fa-fax',
            'Correo Electrónico' => 'fa-envelope',
            default => 'fa-file',
        };
    }

    public function direccionIcon(string $dir): string
    {
        return $dir === 'Recibido' ? 'fa-arrow-down' : 'fa-arrow-up';
    }

    public function tipoAdjuntoIcon(string $fileType): string
    {
        if (str_starts_with($fileType, 'image/')) {
            return 'fa-image';
        }
        if (str_starts_with($fileType, 'application/pdf')) {
            return 'fa-file-pdf';
        }
        if (str_starts_with($fileType, 'application/msword') || str_contains($fileType, 'word')) {
            return 'fa-file-word';
        }

        return 'fa-file';
    }

    // ---------------------------------------------------------------
    // RENDER
    // ---------------------------------------------------------------
    public function render()
    {
        return view('livewire.landing.novedades-cerradas', [
            'guardias' => $this->guardiasCerradas,
            'guardia' => $this->guardiaSeleccionada,
            'adjuntosRecibidos' => $this->adjuntosRecibidos,
            'adjuntosExpedidos' => $this->adjuntosExpedidos,
            'guardiasDelMes' => $this->vista === 'calendario' ? $this->guardiasDelMes : collect(),
            'diasCalendario' => $this->vista === 'calendario' ? $this->diasCalendario : [],
        ]);
    }
}
