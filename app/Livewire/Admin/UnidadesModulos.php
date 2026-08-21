<?php

namespace App\Livewire\Admin;

use App\Models\Comision;
use App\Models\NovedadRancho;
use App\Models\Pase;
use App\Models\Unidad;
use App\Models\UnidadModulo;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Administración de las listas curadas de unidades por módulo (pivot unidad_modulo).
 *
 * Matriz Unidades × Módulos (UnidadModulo::MODULOS): cada checkbox tilda/destilda
 * la fila del pivot en el acto (sin botón "Guardar"). La curación solo afecta los
 * selectores FUTUROS que consumen Unidad::curadasPara($modulo); nunca borra datos
 * ya guardados que referencien la unidad.
 */
class UnidadesModulos extends Component
{
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    public function mount()
    {
        $this->authorize('viewAny', UnidadModulo::class);
    }

    #[Computed]
    public function unidades()
    {
        return Unidad::query()
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Mapa "unidadId:modulo" => true con las filas existentes del pivot.
     */
    #[Computed]
    public function pivotes(): array
    {
        return UnidadModulo::query()
            ->get(['unidad_id', 'modulo'])
            ->mapWithKeys(fn ($fila) => ["{$fila->unidad_id}:{$fila->modulo}" => true])
            ->all();
    }

    /**
     * Usos actuales de cada unidad en datos guardados (contexto para el usuario,
     * NO bloquea nada). Fuente única por tabla: una query agrupada por unidad_id.
     *
     * @return array [unidad_id => ['total' => int, 'detalle' => string]]
     */
    #[Computed]
    public function usosPorUnidad(): array
    {
        $ids = $this->unidades->modelKeys();

        if (empty($ids)) {
            return [];
        }

        $porUnidad = [];

        // Relaciones belongsTo directas contra unidades.id
        $fuentesDirectas = [
            'Usuarios' => User::query(),
            'Vehículos' => Vehiculo::query(),
            'Comisiones' => Comision::query(),
            'Pases' => Pase::query(),
            'Novedades de Rancho' => NovedadRancho::query(),
        ];

        foreach ($fuentesDirectas as $etiqueta => $query) {
            $filas = $query->whereIn('unidad_id', $ids)
                ->selectRaw('unidad_id, COUNT(*) as agregado')
                ->groupBy('unidad_id')
                ->get();

            foreach ($filas as $fila) {
                $porUnidad[$fila->unidad_id][$etiqueta] = (int) $fila->agregado;
            }
        }

        // Apoyos S-4: relación N:M vía pivot apoyo_unidad (respetando soft deletes)
        $filasApoyos = DB::table('apoyo_unidad')
            ->join('apoyos', 'apoyos.id', '=', 'apoyo_unidad.apoyo_id')
            ->whereNull('apoyos.deleted_at')
            ->whereIn('apoyo_unidad.unidad_id', $ids)
            ->selectRaw('apoyo_unidad.unidad_id as unidad_id, COUNT(DISTINCT apoyo_unidad.apoyo_id) as agregado')
            ->groupBy('apoyo_unidad.unidad_id')
            ->get();

        foreach ($filasApoyos as $fila) {
            $porUnidad[$fila->unidad_id]['Apoyos'] = (int) $fila->agregado;
        }

        $resultado = [];

        foreach ($porUnidad as $unidadId => $conteos) {
            arsort($conteos);

            $resultado[$unidadId] = [
                'total' => array_sum($conteos),
                'detalle' => collect($conteos)
                    ->map(fn ($cantidad, $etiqueta) => "{$cantidad} {$etiqueta}")
                    ->implode(' · '),
            ];
        }

        return $resultado;
    }

    /**
     * Crea o borra la fila del pivot para la combinación Unidad × Módulo.
     */
    public function toggle(int $unidadId, string $modulo)
    {
        try {
            $this->authorize('update', UnidadModulo::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        if (! in_array($modulo, UnidadModulo::MODULOS, true)) {
            $this->errorMsg = 'Módulo inválido.';
            return;
        }

        $unidad = Unidad::find($unidadId);

        if (! $unidad) {
            $this->errorMsg = 'La unidad no existe.';
            return;
        }

        $etiqueta = UnidadModulo::ETIQUETAS[$modulo] ?? $modulo;

        $this->loading = true;

        try {
            $pivot = UnidadModulo::query()
                ->where('unidad_id', $unidadId)
                ->where('modulo', $modulo)
                ->first();

            if ($pivot) {
                $pivot->delete();
                $this->successMsg = "\"{$unidad->nombre}\" quitada de \"{$etiqueta}\". Los datos ya guardados no se modifican.";
            } else {
                UnidadModulo::create([
                    'unidad_id' => $unidadId,
                    'modulo' => $modulo,
                ]);
                $this->successMsg = "\"{$unidad->nombre}\" agregada a \"{$etiqueta}\".";
            }

            unset($this->pivotes);
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al actualizar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.unidades-modulos.index', [
            'modulos' => UnidadModulo::MODULOS,
            'etiquetas' => UnidadModulo::ETIQUETAS,
            'unidades' => $this->unidades,
            'pivotes' => $this->pivotes,
            'usosPorUnidad' => $this->usosPorUnidad,
        ]);
    }
}
