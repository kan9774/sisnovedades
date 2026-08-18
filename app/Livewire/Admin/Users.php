<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado de vista: activos o papelera ──
    public $viewMode = 'activos';

    // ── Eliminación permanente (papelera) ──
    public $usuarioAEliminarId = null;

    // ── Eliminación permanente incompleto ──
    public $incompletoAEliminarId = null;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', User::class);
    }

    // ── Navegación entre vistas ──
    public function verPapelera()
    {
        $this->viewMode = 'papelera';
        $this->resetPage();
    }

    public function verActivos()
    {
        $this->viewMode = 'activos';
        $this->resetPage();
    }

    // ── Consulta principal con caché ──
    #[Computed]
    public function usuarios()
    {
        $baseQuery = auth()->user()->isSuperAdmin()
            ? User::query()
            : User::query()
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                ->where('is_super_admin', false);

        if ($this->viewMode === 'papelera') {
            $query = (clone $baseQuery)
                ->onlyTrashed()
                ->with(['roles', 'grado']);
        } else {
            $query = (clone $baseQuery)
                ->with(['roles', 'grado'])
                ->whereNotNull('perfil_completo_at');
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('segundo_nombre', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('segundo_apellido', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ci', 'like', "%{$search}%");
            });
        }

        if ($this->viewMode === 'papelera') {
            $query->orderByDesc('deleted_at');
        } else {
            $query->leftJoin('grados', 'grados.id', '=', 'users.grado_id')
                ->orderByRaw('grados.orden IS NULL, grados.orden ASC')
                ->select('users.*');
        }

        return $query->paginate(15)->withQueryString();
    }

    // ── Consulta de incompletos con caché ──
    #[Computed]
    public function usuariosIncompletos()
    {
        $baseQuery = auth()->user()->isSuperAdmin()
            ? User::query()
            : User::query()
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                ->where('is_super_admin', false);

        return (clone $baseQuery)
            ->with(['roles', 'grado'])
            ->whereNull('perfil_completo_at')
            ->orderByDesc('created_at')
            ->get();
    }

    // ── REACTIVO: al cambiar búsqueda, forzar reset page ──
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ── LIMPIAR FILTROS ──
    public function limpiarFiltros()
    {
        $this->search = '';
        $this->resetPage();
    }

    // ── PAPELERA: Restaurar ──
    public function restaurar($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        try {
            $this->authorize('delete', $user);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $user->restore();
        $this->successMsg = 'Usuario restaurado correctamente.';
    }

    // ── PAPELERA: Confirmar eliminación permanente ──
    public function confirmarEliminacionPermanente($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        try {
            $this->authorize('delete', $user);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->usuarioAEliminarId = $id;
    }

    // ── PAPELERA: Ejecutar eliminación permanente ──
    public function ejecutarEliminacionPermanente()
    {
        $this->loading = true;
        try {
            $user = User::onlyTrashed()->findOrFail($this->usuarioAEliminarId);

            DB::transaction(function () use ($user) {
                // FK RESTRICT / NO ACTION (requieren borrado explícito)
                DB::table('historial_palomas')->where('user_id', $user->id)->delete();
                DB::table('entregas')->where('usuario_id', $user->id)->delete();
                DB::table('movimientos')->where('usuario_id', $user->id)->delete();
                DB::table('novedades_personal')->where('user_id', $user->id)->delete();

                // FK CASCADE / SET NULL — borrado explícito por seguridad (mismo patrón que destroyIncompleto)
                DB::table('historial_grados')->where('user_id', $user->id)->delete();
                DB::table('historial_estado')->where('user_id', $user->id)->delete();
                DB::table('pases')->where('user_id', $user->id)->delete();
                DB::table('comisiones')->where('user_id', $user->id)->delete();
                DB::table('documentos')->where('subido_por', $user->id)->update(['subido_por' => null]);
                DB::table('item_unidades')->where('responsable_id', $user->id)->update(['responsable_id' => null]);
                DB::table('mantenimientos_vehiculo')->where('registrado_por', $user->id)->update(['registrado_por' => null]);
                DB::table('news')->where('tomado_por_id', $user->id)->update(['tomado_por_id' => null]);
                DB::table('passkeys')->where('user_id', $user->id)->delete();
                DB::table('role_user')->where('user_id', $user->id)->delete();
                DB::table('user_permission')->where('user_id', $user->id)->delete();

                // Relación polimórfica: direcciones y credenciales
                DB::table('direcciones')->where('user_id', $user->id)->delete();
                DB::table('credenciales_civicas')->where('user_id', $user->id)->delete();

                // Guards: captain_id y oficer_id (NOT NULL — borrar registros)
                DB::table('guards')->where('captain_id', $user->id)->delete();
                DB::table('guards')->where('oficer_id', $user->id)->delete();

                $user->forceDelete();
            });

            $this->successMsg = 'Usuario eliminado permanentemente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar permanentemente: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->usuarioAEliminarId = null;
        }
    }

    // ── ELIMINAR (mover a papelera) ──
    public function confirmarEliminacion($id)
    {
        $user = User::findOrFail($id);

        try {
            $this->authorize('delete', $user);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        // Regla extra: no se puede eliminar a un admin (no está en la Policy)
        if ($user->isAdmin()) {
            $this->errorMsg = 'No se puede eliminar a un administrador.';
            return;
        }

        $this->ejecutarEliminacion($user);
    }

    protected function ejecutarEliminacion($user)
    {
        $this->loading = true;
        try {
            $user->delete();
            $this->successMsg = 'Usuario eliminado correctamente. Puedes restaurarlo desde la papelera.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── ELIMINAR POR COMPLETO (incompletos) ──
    public function destroyIncompleto($id)
    {
        $user = User::whereNull('perfil_completo_at')->findOrFail($id);

        try {
            $this->authorize('delete', $user);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        DB::transaction(function () use ($user) {
            // FK RESTRICT / NO ACTION (requieren borrado explícito)
            DB::table('historial_palomas')->where('user_id', $user->id)->delete();
            DB::table('entregas')->where('usuario_id', $user->id)->delete();
            DB::table('movimientos')->where('usuario_id', $user->id)->delete();
            DB::table('novedades_personal')->where('user_id', $user->id)->delete();

            // FK CASCADE / SET NULL — borrado explícito por seguridad
            DB::table('historial_grados')->where('user_id', $user->id)->delete();
            DB::table('historial_estado')->where('user_id', $user->id)->delete();
            DB::table('pases')->where('user_id', $user->id)->delete();
            DB::table('comisiones')->where('user_id', $user->id)->delete();

            $user->forceDelete();
        });

        $this->successMsg = 'Registro incompleto eliminado por completo.';
    }

    // ── PAPELERA: Confirmar eliminación permanente de incompleto ──
    public function confirmarEliminacionPermanenteIncompleto($id)
    {
        $user = User::whereNull('perfil_completo_at')->findOrFail($id);

        try {
            $this->authorize('delete', $user);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->incompletoAEliminarId = $id;
    }

    // ── PAPELERA: Ejecutar eliminación permanente de incompleto ──
    public function ejecutarEliminacionPermanenteIncompleto()
    {
        $this->loading = true;
        try {
            $user = User::whereNull('perfil_completo_at')->findOrFail($this->incompletoAEliminarId);

            DB::transaction(function () use ($user) {
                // FK RESTRICT / NO ACTION (requieren borrado explícito)
                DB::table('historial_palomas')->where('user_id', $user->id)->delete();
                DB::table('entregas')->where('usuario_id', $user->id)->delete();
                DB::table('movimientos')->where('usuario_id', $user->id)->delete();
                DB::table('novedades_personal')->where('user_id', $user->id)->delete();

                // FK CASCADE / SET NULL — borrado explícito por seguridad
                DB::table('historial_grados')->where('user_id', $user->id)->delete();
                DB::table('historial_estado')->where('user_id', $user->id)->delete();
                DB::table('pases')->where('user_id', $user->id)->delete();
                DB::table('comisiones')->where('user_id', $user->id)->delete();

                $user->forceDelete();
            });

            $this->successMsg = 'Registro incompleto eliminado por completo.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar permanentemente: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->incompletoAEliminarId = null;
        }
    }

    // ── RENDER ──
    public function render()
    {
        return view('livewire.admin.users.index', [
            'usuarios' => $this->usuarios(),
            'usuariosIncompletos' => $this->usuariosIncompletos(),
        ]);
    }
}
