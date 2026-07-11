<?php

use App\Jobs\EnviarNovedadGuardiaMail;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Guard $guardia;
    public bool $soloPendientes = true;

    public function mount(Guard $guardia): void
    {
        $this->guardia = $guardia;
    }

    #[Computed]
    public function fallos()
    {
        return DB::table('guardia_correos_fallidos')
            ->where('guardia_id', $this->guardia->id)
            ->when($this->soloPendientes, fn ($q) => $q->whereNull('resuelto_at'))
            ->orderByDesc('created_at')
            ->get();
    }

    public function reintentar(int $id): void
    {
        $fallo = DB::table('guardia_correos_fallidos')
            ->where('id', $id)
            ->where('guardia_id', $this->guardia->id)
            ->first();

        if (! $fallo) {
            return;
        }

        $usuario = User::findOrFail($fallo->user_id);
        $nombreRemitente = Auth::user()->name . ' ' . Auth::user()->last_name;

        EnviarNovedadGuardiaMail::dispatch($this->guardia, $usuario, $nombreRemitente);

        DB::table('guardia_correos_fallidos')->where('id', $id)->update([
            'resuelto_at' => now(),
            'updated_at'  => now(),
        ]);

        unset($this->fallos);
        $this->dispatch('correos-fallidos-actualizado');
    }

    public function marcarResuelto(int $id): void
    {
        DB::table('guardia_correos_fallidos')
            ->where('id', $id)
            ->where('guardia_id', $this->guardia->id)
            ->update([
                'resuelto_at' => now(),
                'updated_at'  => now(),
            ]);

        unset($this->fallos);
        $this->dispatch('correos-fallidos-actualizado');
    }
};