<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'      => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'grade'     => ['nullable', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        $rolVisitante = Rol::where('name', 'visitante')->first();

        return User::create([
            'name'      => $data['name'],
            'last_name' => $data['last_name'],
            'grade'     => $data['grade'] ?? 'Civil',
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'rol_id'    => $rolVisitante->id,
            'status'    => 'active',
        ]);
    }
}