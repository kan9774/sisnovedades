<?php

namespace App\Http\Controllers;

use App\Concerns\PasswordValidationRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForzarCambioPasswordController extends Controller
{
    use PasswordValidationRules;

    public function edit()
    {
        // Si ya la cambió, no tiene nada que hacer acá.
        if (! auth()->user()->must_change_password) {
            return redirect()->route('home');
        }

        return view('auth.forzar-cambio-password');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'password' => $this->passwordRules(),
        ]);

        $user = auth()->user();
        $user->password = Hash::make($data['password']);
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('home')->with('success', 'Contraseña actualizada. Ya podés usar el sistema con normalidad.');
    }
}