<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Rol;
use App\Models\Unidad;
use App\Models\User;
use App\Notifications\NuevoUsuarioRegistradoNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'unidad_id' => ['required', 'exists:unidades,id'],
        ])->validate();

        $rolVisitante = Rol::where('name', 'visitante')->first();

        $user = User::create([
            'name' => $input['name'],
            'last_name' => $input['last_name'],
            'grade' => $input['grade'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'rol_id' => $rolVisitante?->id,
            'unidad_id' => $input['unidad_id'],
            'status' => 'active',
        ]);

        // Aviso a admins/superadmins para que decidan si le asignan un rol
        // distinto o lo dejan como visitante. Solo se dispara acá (registro
        // público) — no cuando un admin crea el usuario desde el panel.
        $this->notificarAdmins($user);

        return $user;
    }

    protected function notificarAdmins(User $usuarioRegistrado): void
    {
        $admins = User::where('is_super_admin', true)
            ->orWhereHas('rol', fn ($q) => $q->where('name', 'admin'))
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NuevoUsuarioRegistradoNotification($usuarioRegistrado));
        }
    }
}