<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Traits\UsesBootstrapPagination;
use Spatie\Activitylog\Models\Activity;

class Logs extends Component
{
    use WithPagination, UsesBootstrapPagination;

    #[Url(as: 'log_name')]
    public $logName = '';

    #[Url]
    public $event = '';

    #[Url]
    public $userId = '';

    #[Url]
    public $desde = '';

    #[Url]
    public $hasta = '';

    #[Computed]
    public function logs()
    {
        return Activity::with('causer')
            ->when($this->logName, fn($q) => $q->where('log_name', $this->logName))
            ->when($this->event, fn($q) => $q->where('event', $this->event))
            ->when($this->userId, fn($q) => $q->where('causer_id', $this->userId))
            ->when($this->desde, fn($q) => $q->whereDate('created_at', '>=', $this->desde))
            ->when($this->hasta, fn($q) => $q->whereDate('created_at', '<=', $this->hasta))
            ->latest()
            ->paginate(25);
    }

    #[Computed]
    public function logNames()
    {
        return Activity::distinct()->pluck('log_name')->filter();
    }

    #[Computed]
    public function eventos()
    {
        return Activity::distinct()->pluck('event')->filter();
    }

    public function mount()
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny-log');
    }

    public function updatedLogName() { $this->resetPage(); }
    public function updatedEvent() { $this->resetPage(); }
    public function updatedUserId() { $this->resetPage(); }
    public function updatedDesde() { $this->resetPage(); }
    public function updatedHasta() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->logName = '';
        $this->event = '';
        $this->userId = '';
        $this->desde = '';
        $this->hasta = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.logs.index', [
            'logs' => $this->logs(),
            'logNames' => $this->logNames(),
            'eventos' => $this->eventos(),
        ]);
    }
}
