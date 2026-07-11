<?php

namespace App\Jobs;

use App\Mail\GuardiaNovedadesMail;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarNovedadGuardiaMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public Guard $guardia,
        public User $usuario,
        public string $nombreRemitente,
    ) {}

    public function handle(): void
    {
        Mail::to($this->usuario->email)->send(
            new GuardiaNovedadesMail($this->guardia, $this->nombreRemitente)
        );
    }

    public function failed(Throwable $exception): void
    {
        DB::table('guardia_correos_fallidos')->insert([
            'guardia_id' => $this->guardia->id,
            'user_id'    => $this->usuario->id,
            'email'      => $this->usuario->email,
            'motivo'     => $exception->getMessage(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}