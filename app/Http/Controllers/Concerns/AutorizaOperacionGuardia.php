<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Guard;
use Illuminate\Support\Facades\Auth;

trait AutorizaOperacionGuardia
{
    protected function autorizarOperacion(Guard $guardia): void
    {
        abort_if($guardia->status !== 'open', 403, 'La guardia está cerrada.');

        $user = Auth::user();
        $puedeOperar = $guardia->captain_id === $user->id
            || $guardia->oficer_id === $user->id
            || $guardia->escribiente->contains('id', $user->id)
            || $user->isAdmin();

        abort_unless($puedeOperar, 403);
    }
}