<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Rol;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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

        return User::create([
            'name' => $input['name'],
            'last_name' => $input['last_name'],
            'grade' => $input['grade'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'rol_id' => $rolVisitante?->id,
            'unidad_id' => $input['unidad_id'],
            'status' => 'active',
        ]);
    }
}