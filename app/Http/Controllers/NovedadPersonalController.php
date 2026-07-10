<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaOperacionGuardia;
use App\Models\Guard;
use App\Models\NovedadPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NovedadPersonalController extends Controller
{
    use AutorizaOperacionGuardia;

    public function store(Request $request, Guard $guardia)
    {
        $this->autorizarOperacion($guardia);

        $data = $request->validate([
            'hora'  => 'required|date_format:H:i',
            'tipo'  => 'required|string|max:100',
            'texto' => 'required|string',
        ]);

        $guardia->novedadesPersonal()->create([...$data, 'user_id' => Auth::id()]);

        return back()->with('success', 'Novedad de personal registrada.');
    }

    public function destroy(Guard $guardia, NovedadPersonal $novedadPersonal)
    {
        $this->autorizarOperacion($guardia);
        abort_unless($novedadPersonal->guard_id === $guardia->id, 404);

        $novedadPersonal->delete();

        return back()->with('success', 'Novedad de personal eliminada.');
    }
}