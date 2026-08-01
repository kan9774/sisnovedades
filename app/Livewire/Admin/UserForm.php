<?php

namespace App\Livewire\Admin;

use App\Models\Departamento;
use App\Models\Grado;
use App\Models\Oficina;
use App\Models\Rol;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    public ?User $user = null;

    public string $activeTab = 'general';

    // Tab: General (incluye datos personales)
    public $ci = '';
    public $fecha_nacimiento;
    public $grado_id;
    public $name = '';
    public $segundo_nombre = '';
    public $last_name = '';
    public $segundo_apellido = '';
    public $email = '';
    public $unidad_id;
    public $oficina_id;
    public $password = '';
    public $password_confirmation = '';
    public $is_super_admin = false;
    public $digito_verificador = null;

    // Tab: Dirección
    public $departamento_id;
    public $localidad = '';
    public $calle = '';
    public $numero = '';
    public $esquina = '';
    public $apartamento = '';
    public $barrio = '';
    public $codigo_postal = '';
    public $referencia = '';

    // Tab: Roles
    public array $roles = [];

    public function mount(?User $user = null): void
    {
        $this->user = $user;

        if ($this->user?->exists) {
            $this->authorize('update', $this->user);
        } else {
            $this->authorize('create', User::class);

            // Todo ingreso nuevo arranca en Sdo. 1° (el grado de menor
            // jerarquía = mayor `orden`) por defecto. Sigue siendo
            // editable desde el <select>, para el caso de un pase que
            // llega con otro grado.
            $this->grado_id = Grado::where('activo', true)->orderByDesc('orden')->value('id');
        }

        if ($this->user?->exists) {
            $this->ci = $this->user->ci;
            $this->fecha_nacimiento = $this->user->fecha_nacimiento?->format('Y-m-d');
            $this->grado_id = $this->user->grado_id;
            $this->name = $this->user->name;
            $this->segundo_nombre = $this->user->segundo_nombre;
            $this->last_name = $this->user->last_name;
            $this->segundo_apellido = $this->user->segundo_apellido;
            $this->email = $this->user->email;
            $this->unidad_id = $this->user->unidad_id;
            $this->oficina_id = $this->user->oficina_id;
            $this->is_super_admin = (bool) $this->user->is_super_admin;

            $this->calcularDigitoVerificador();

            $this->roles = $this->user->roles->pluck('id')->toArray();

            if ($direccion = $this->user->direccionPrincipal) {
                $this->departamento_id = $direccion->departamento_id;
                $this->localidad = $direccion->localidad;
                $this->calle = $direccion->calle;
                $this->numero = $direccion->numero;
                $this->esquina = $direccion->esquina;
                $this->apartamento = $direccion->apartamento;
                $this->barrio = $direccion->barrio;
                $this->codigo_postal = $direccion->codigo_postal;
                $this->referencia = $direccion->referencia;
            }
        }
    }

    /**
     * Recalcula el dígito verificador cada vez que cambia la C.I.
     * (se dispara automáticamente por Livewire al actualizarse $ci).
     */
    public function updatedCi(): void
    {
        $this->calcularDigitoVerificador();
    }

    /**
     * Preview en vivo del dígito verificador mientras el usuario tipea,
     * antes de guardar. Reusa el mismo algoritmo que User::setCiAttribute()
     * aplica al persistir, para que nunca puedan desincronizarse.
     */
    private function calcularDigitoVerificador(): void
    {
        if (!$this->ci || !preg_match('/^\d{6,7}$/', $this->ci)) {
            $this->digito_verificador = null;
            return;
        }

        $this->digito_verificador = User::calcularDigitoVerificadorCi($this->ci);
    }

    /**
     * Los datos básicos (C.I. a Segundo Apellido), el acceso (Email y
     * Contraseña) y los Roles de un usuario ya guardado solo pueden ser
     * modificados por admin o superadmin. Un usuario nuevo (todavía no
     * guardado) siempre es editable.
     */
    public function puedeEditarDatosBasicos(): bool
    {
        if (!$this->user?->exists) {
            return true;
        }

        return auth()->user()->isSuperAdmin()
            || auth()->user()->roles->contains('name', 'admin');
    }

    protected function rules(): array
    {
        $userId = $this->user?->id;

        return [
            'ci' => [
                'nullable',
                'digits_between:6,8',
                Rule::unique('users', 'ci')->ignore($userId),
            ],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'grado_id' => ['required', 'exists:grados,id'],
            'name' => ['required', 'string', 'max:255'],
            'segundo_nombre' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'segundo_apellido' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'unidad_id' => ['required', 'exists:unidades,id'],
            'oficina_id' => ['nullable', 'exists:oficinas,id'],
            'password' => [$this->user?->exists ? 'nullable' : 'required', 'confirmed', 'min:8'],

            'departamento_id' => ['nullable', 'exists:departamentos,id'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'calle' => ['required_with:departamento_id', 'nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:255'],
            'esquina' => ['nullable', 'string', 'max:255'],
            'apartamento' => ['nullable', 'string', 'max:255'],
            'barrio' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'referencia' => ['nullable', 'string', 'max:255'],

            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:rols,id'],
        ];
    }

    protected array $messages = [
        'roles.required' => 'Debés seleccionar al menos un rol.',
        'password.required' => 'La contraseña es obligatoria para un usuario nuevo.',
        'calle.required_with' => 'Si seleccionás un departamento, la calle es obligatoria.',
        'ci.unique' => 'Ya existe un usuario registrado con esa cédula.',
    ];

    /**
     * Valida solo los campos de la tab activa al cambiar de pestaña,
     * así el usuario ve errores sin esperar al submit final.
     */
    public function setTab(string $tab): void
    {
        $camposPorTab = [
            'general' => ['ci', 'fecha_nacimiento', 'grado_id', 'name', 'segundo_nombre', 'last_name', 'segundo_apellido', 'unidad_id', 'oficina_id'],
            'direccion' => ['departamento_id', 'localidad', 'calle', 'numero', 'esquina', 'apartamento', 'barrio', 'codigo_postal', 'referencia'],
            'roles' => ['email', 'password', 'roles'],
        ];

        if (isset($camposPorTab[$this->activeTab])) {
            $rulesTab = collect($this->rules())
                ->only($camposPorTab[$this->activeTab])
                ->toArray();

            if (!empty($rulesTab)) {
                $this->validate($rulesTab, $this->messages);
            }
        }

        $this->activeTab = $tab;
    }

    /**
     * Registra en historial_grados el cambio de grado, si hubo uno.
     * Detecta automáticamente si es ascenso o degradación comparando el
     * campo `orden` del grado nuevo contra el del anterior. En esta app
     * `orden` va de mayor jerarquía a menor (Coronel = 3, Sdo. 1° = 15),
     * así que un ascenso es cuando el `orden` nuevo es MENOR que el
     * anterior, y una degradación cuando es MAYOR. Si no había grado
     * anterior (usuario nuevo), lo trata como el grado de ingreso.
     *
     * Nace sin numero_orden/resolución/observaciones: esos datos se van
     * a cargar aparte, desde una pantalla de historial dedicada.
     */
    private function registrarCambioDeGrado(?int $gradoAnteriorId, int $gradoNuevoId): void
    {
        if ($gradoAnteriorId === $gradoNuevoId) {
            return;
        }

        $tipo = 'ascenso';

        if ($gradoAnteriorId) {
            $ordenAnterior = Grado::find($gradoAnteriorId)?->orden;
            $ordenNuevo = Grado::find($gradoNuevoId)?->orden;

            if ($ordenAnterior !== null && $ordenNuevo !== null && $ordenNuevo > $ordenAnterior) {
                $tipo = 'degradacion';
            }
        }

        $this->user->historialGrados()->create([
            'grado_id' => $gradoNuevoId,
            'tipo' => $tipo,
            'fecha_cambio' => now()->toDateString(),
        ]);
    }

    /**
     * Quita el rol "admin" del listado si quien está armando/editando el
     * usuario no es SuperAdmin, sin importar lo que haya venido en el form.
     *
     * @param array<int> $rolesIds
     * @return array<int>
     */
    private function filtrarRolesPermitidos(array $rolesIds): array
    {
        if (auth()->user()->isSuperAdmin()) {
            return $rolesIds;
        }

        $adminRolId = Rol::where('name', 'admin')->value('id');

        return array_values(array_diff($rolesIds, [$adminRolId]));
    }

    public function save(): void
    {
        if ($this->user?->exists && !$this->puedeEditarDatosBasicos()) {
            $this->ci = $this->user->ci;
            $this->fecha_nacimiento = $this->user->fecha_nacimiento?->format('Y-m-d');
            $this->grado_id = $this->user->grado_id;
            $this->name = $this->user->name;
            $this->segundo_nombre = $this->user->segundo_nombre;
            $this->last_name = $this->user->last_name;
            $this->segundo_apellido = $this->user->segundo_apellido;
            $this->email = $this->user->email;
            $this->password = '';
            $this->password_confirmation = '';
            $this->roles = $this->user->roles->pluck('id')->toArray();
        }

        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $gradoAnteriorId = $this->user?->grado_id;

            $data = [
                'ci' => $validated['ci'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'grado_id' => $validated['grado_id'],
                'name' => $validated['name'],
                'segundo_nombre' => $validated['segundo_nombre'],
                'last_name' => $validated['last_name'],
                'segundo_apellido' => $validated['segundo_apellido'],
                'email' => $validated['email'],
                'unidad_id' => $validated['unidad_id'],
                'oficina_id' => $validated['oficina_id'],
            ];

            if (auth()->user()->isSuperAdmin() && $this->user?->id !== auth()->id()) {
                $data['is_super_admin'] = $this->is_super_admin;
            }

            $esNuevo = !$this->user?->exists;

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
                $data['must_change_password'] = true;
            }

            if ($esNuevo) {
                $data['status'] = 'active';
                $this->user = User::create($data);
            } else {
                $this->user->update($data);
            }

            $this->registrarCambioDeGrado($gradoAnteriorId, $validated['grado_id']);

            $roles = $this->filtrarRolesPermitidos($this->roles);
            $this->user->roles()->sync($roles);

            if ($this->departamento_id) {
                $this->user->direcciones()->updateOrCreate(
                    ['es_principal' => true],
                    [
                        'tipo' => 'particular',
                        'departamento_id' => $this->departamento_id,
                        'localidad' => $this->localidad,
                        'calle' => $this->calle,
                        'numero' => $this->numero,
                        'esquina' => $this->esquina,
                        'apartamento' => $this->apartamento,
                        'barrio' => $this->barrio,
                        'codigo_postal' => $this->codigo_postal,
                        'referencia' => $this->referencia,
                        'es_principal' => true,
                    ]
                );
            }

            session()->flash('success', $esNuevo ? 'Usuario creado correctamente.' : 'Usuario actualizado correctamente.');
        });

        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        $rolesDisponibles = auth()->user()->isSuperAdmin()
            ? Rol::orderBy('name')->get()
            : Rol::where('name', '!=', 'admin')->orderBy('name')->get();

        $unidades = Unidad::where('activo', true)
            ->when($this->unidad_id, fn($q) => $q->orWhere('id', $this->unidad_id))
            ->orderBy('nombre')
            ->get();

        $oficinas = Oficina::where('activo', true)
            ->when($this->oficina_id, fn($q) => $q->orWhere('id', $this->oficina_id))
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.user-form', [
            'grados' => Grado::where('activo', true)->orderBy('orden')->get(),
            'unidades' => $unidades,
            'oficinas' => $oficinas,
            'rolesDisponibles' => $rolesDisponibles,
            'departamentos' => Departamento::orderBy('nombre')->get(),
        ]);
    }
}
