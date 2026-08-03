<?php

namespace App\Livewire\Admin;

use App\Models\Grado;
use App\Models\Pase;
use App\Models\Rol;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class UserWizard extends Component
{
    public int $step = 1;

    public ?User $user = null;

    // Paso 1
    public string $ci = '';
    public ?int $digito_verificador = null;

    // Paso 2
    public string $tipo = 'alta'; // 'alta' | 'pase'
    public string $grado_id = '';
    public string $fecha = '';
    public string $unidad_id = '';

    // Paso 3
    public string $name = '';
    public string $segundo_nombre = '';
    public string $last_name = '';
    public string $segundo_apellido = '';
    public string $fecha_nacimiento = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $roles = [];

    /**
     * Si se recibe un $user existente (siempre uno incompleto, con
     * perfil_completo_at en null), se retoma el wizard en el paso que
     * corresponda en vez de arrancar de cero desde el Paso 1.
     */
    public function mount(?User $user = null): void
    {
        $this->authorize('create', User::class);
        if ($user) {

            abort_if($user->perfil_completo_at !== null, 404);

            $this->user = $user;
            $this->ci = $user->ci ?? '';
            $this->updatedCi();

            $paseVigente = $user->paseVigente();
            if ($user->grado_id && $paseVigente) {
                // Ya completó el Paso 2: retoma en el Paso 3 con los
                // datos personales que falten.
                $this->step = 3;
                $this->name = $user->name ?? '';
                $this->segundo_nombre = $user->segundo_nombre ?? '';
                $this->last_name = $user->last_name ?? '';
                $this->segundo_apellido = $user->segundo_apellido ?? '';
                $this->fecha_nacimiento = optional($user->fecha_nacimiento)->toDateString() ?? '';
                $this->email = $user->email ?? '';
            } else {
                // Solo tiene la C.I. cargada (Paso 1): retoma en el Paso 2.
                $this->step = 2;
                $this->fecha = now()->toDateString();
                $this->grado_id = (string) Grado::where('activo', true)->orderByDesc('orden')->value('id');
            }

            return;
        }

        $this->fecha = now()->toDateString();
    }

    #[Computed]
    public function grados()
    {
        return Grado::where('activo', true)->orderBy('orden')->get();
    }

    #[Computed]
    public function unidades()
    {
        return Unidad::where('activo', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function rolesDisponibles()
    {
        return auth()->user()->isSuperAdmin()
            ? Rol::orderBy('name')->get()
            : Rol::where('name', '!=', 'admin')->orderBy('name')->get();
    }

    public function updatedCi(): void
    {
        if ($this->ci && preg_match('/^\d{6,7}$/', $this->ci)) {
            $this->digito_verificador = User::calcularDigitoVerificadorCi($this->ci);
        } else {
            $this->digito_verificador = null;
        }
    }

    /**
     * Paso 1: valida C.I. única y crea el User ya mismo (solo con la
     * C.I. cargada). A partir de acá el resto de los pasos actúan
     * sobre este mismo registro, no sobre datos sueltos en memoria.
     */
    public function guardarPaso1(): void
    {
        $this->validate([
            'ci' => ['required', 'digits_between:6,8', Rule::unique('users', 'ci')],
        ], [
            'ci.unique' => 'Ya existe un usuario registrado con esa cédula.',
        ]);

        $this->user = User::create(['ci' => $this->ci]);

        // Default de grado para el camino "alta": Sdo. 1° (el de `orden`
        // más alto entre los activos), igual que hacía UserForm::mount().
        $this->grado_id = (string) Grado::where('activo', true)->orderByDesc('orden')->value('id');

        $this->step = 2;
    }

    public function updatedTipo(): void
    {
        if ($this->tipo === 'alta') {
            $this->grado_id = (string) Grado::where('activo', true)->orderByDesc('orden')->value('id');
        }
    }

    /**
     * Paso 2: registra grado inicial, alta en historial_estado, y
     * primer pase (unidad). fecha_desde del pase depende del tipo:
     * literal si es alta (ingreso real), o corrida al mes siguiente
     * si es pase (viene de otra unidad, sigue la convención de cierre
     * de mes).
     */
    public function guardarPaso2(): void
    {
        $this->validate([
            'grado_id' => ['required', 'exists:grados,id'],
            'fecha' => ['required', 'date'],
            'unidad_id' => ['required', 'exists:unidades,id'],
        ]);

        DB::transaction(function () {
            $this->user->update(['grado_id' => $this->grado_id]);

            $this->user->historialGrados()->create([
                'grado_id' => $this->grado_id,
                'tipo' => 'ascenso', // sin grado anterior: siempre se trata como ingreso
                'fecha_cambio' => $this->fecha,
            ]);

            $this->user->historialEstados()->create([
                'tipo' => 'alta',
                'fecha' => $this->fecha,
            ]);

            $this->user->pases()->create([
                'unidad_id' => $this->unidad_id,
                'fecha_desde' => $this->tipo === 'pase'
                    ? Pase::fechaDesdeParaPase($this->fecha)
                    : $this->fecha,
            ]);
        });

        $this->step = 3;
    }

    public function volverPaso(int $paso): void
    {
        $this->step = $paso;
    }

    /**
     * Paso 3: completa los datos personales, credenciales y roles.
     * Recién acá queda "perfil_completo_at" cargado — es lo que
     * distingue a un usuario real de uno abandonado a mitad de wizard.
     */
    public function guardarPaso3(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'segundo_nombre' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'segundo_apellido' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => ['required', 'confirmed', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:rols,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $this->user->update([
                'name' => $validated['name'],
                'segundo_nombre' => $validated['segundo_nombre'],
                'last_name' => $validated['last_name'],
                'segundo_apellido' => $validated['segundo_apellido'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'must_change_password' => true,
                'perfil_completo_at' => now(),
            ]);

            $this->user->roles()->sync($validated['roles']);
        });

        session()->flash('success', 'Usuario creado correctamente.');
        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        return view('livewire.admin.user-wizard');
    }
}
