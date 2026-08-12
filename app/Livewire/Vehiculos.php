<?php

namespace App\Livewire;

use App\Models\TipoCombustible;
use App\Models\TipoLubricante;
use App\Models\TipoRodado;
use App\Models\TipoVehiculo;
use App\Models\Unidad;
use App\Models\Vehiculo;
use App\Models\VehiculoActa;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Vehiculos extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;
    use WithFileUploads;

    // ── TEST MANUAL (caso de uso carpetas por matrícula) ──
    // 1) Crear vehículo con matrícula "AA-123", subir un acta PDF.
    //    Verificar: storage\app\public\actas\AA_123\{random}.pdf existe.
    // 2) Editar vehículo, cambiar matrícula a "BB-456", guardar.
    //    Verificar: actas\AA_123\ ya no existe.
    //    Verificar: actas\BB_456\{random}.pdf existe.
    //    Verificar: registro VehiculoActa en BD tiene path actualizado a
    //               actas/BB_456/{random}.pdf.
    // 3) Editar de nuevo, volver a "AA-123", subir otro acta.
    //    Verificar: ambos archivos están en actas\AA_123\.
    //    Verificar: ambos registros VehiculoActa apuntan a actas/AA_123/...

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formVehiculoId = null;

    // ═══════════════════════════════════════════
    // DATOS GENERALES
    // ═══════════════════════════════════════════
    public $formMatricula = '';
    public $formMarca = '';
    public $formModelo = '';
    public $formVehiculo = '';
    public $formNumeroChasis = '';
    public $formNumeroMotor = '';
    public $formEjes = 1;
    public $formDescripcion = '';
    public $formEstado = 'verde';

    // ═══════════════════════════════════════════
    // CATÁLOGOS
    // ═══════════════════════════════════════════
    public $formUnidadId = null;
    public $formTipoVehiculoId = null;
    public $formTipoCombustibleId = null;
    public $formTipoLubricanteId = null;
    public $formTipoRodadoId = null;

    // ═══════════════════════════════════════════
    // TÉCNICOS
    // ═══════════════════════════════════════════
    public $formConsumoLitrosPorKm = null;
    public $formSinCuentakilometros = false;
    public $formActivo = true;

    // ═══════════════════════════════════════════
    // DOCUMENTACIÓN (MÚLTIPLES ACTAS)
    // ═══════════════════════════════════════════
    public $queuedActaPaths = [];
    public $formActasExistentes = [];
    public $singleActaUpload = null;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $confirmDeleteId = null;
    public $confirmForceDeleteId = null;

    // ── Papelera ──
    public $vistaPapelera = false;

    // ── Estado del modal de detalle (show) ──
    public $showDetalle = false;
    public $detalleVehiculoId = null;
    public $detalleVehiculo = null;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', Vehiculo::class);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function vehiculos()
    {
        $query = Vehiculo::with(['tipoVehiculo', 'tipoCombustible', 'tipoLubricante', 'tipoRodado', 'unidad'])
            ->orderBy('matricula');

        if ($this->vistaPapelera) {
            $query = Vehiculo::onlyTrashed()->with(['tipoVehiculo', 'tipoCombustible', 'tipoLubricante', 'tipoRodado', 'unidad'])
                ->orderBy('matricula');
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('matricula', 'like', '%' . $this->search . '%')
                  ->orWhere('marca', 'like', '%' . $this->search . '%')
                  ->orWhere('modelo', 'like', '%' . $this->search . '%')
                  ->orWhere('vehiculo', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function catalogos()
    {
        return [
            'unidades' => Unidad::where('activo', true)->orderBy('nombre')->get(),
            'tiposVehiculo' => TipoVehiculo::where('activo', true)->orderBy('nombre')->get(),
            'tiposCombustible' => TipoCombustible::where('activo', true)->orderBy('nombre')->get(),
            'tiposLubricante' => TipoLubricante::where('activo', true)->orderBy('nombre')->get(),
            'tiposRodado' => TipoRodado::where('activo', true)->orderBy('nombre')->get(),
        ];
    }

    // ── REACTIVO: al cambiar estado, forzar activo=false si es rojo/negro ──
    public function updatedFormEstado()
    {
        if (in_array($this->formEstado, ['rojo', 'negro'])) {
            $this->formActivo = false;
        }
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        try {
            $this->authorize('create', Vehiculo::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
        $this->dispatch('abrir-modal-vehiculo');
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $vehiculoId)
    {
        $vehiculo = Vehiculo::findOrFail($vehiculoId);

        try {
            $this->authorize('update', $vehiculo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formVehiculoId = $vehiculo->id;

        // Datos generales
        $this->formMatricula = $vehiculo->matricula;
        $this->formMarca = $vehiculo->marca;
        $this->formModelo = $vehiculo->modelo;
        $this->formVehiculo = $vehiculo->vehiculo;
        $this->formNumeroChasis = $vehiculo->numero_chasis;
        $this->formNumeroMotor = $vehiculo->numero_motor;
        $this->formEjes = $vehiculo->ejes;
        $this->formDescripcion = $vehiculo->descripcion;
        $this->formEstado = $vehiculo->estado;

        // Catálogos
        $this->formUnidadId = $vehiculo->unidad_id;
        $this->formTipoVehiculoId = $vehiculo->tipo_vehiculo_id;
        $this->formTipoCombustibleId = $vehiculo->tipo_combustible_id;
        $this->formTipoLubricanteId = $vehiculo->tipo_lubricante_id;
        $this->formTipoRodadoId = $vehiculo->tipo_rodado_id;

        // Técnicos
        $this->formConsumoLitrosPorKm = $vehiculo->consumo_litros_por_km;
        $this->formSinCuentakilometros = (bool) $vehiculo->sin_cuentakilometros;
        $this->formActivo = (bool) $vehiculo->activo;

        // Documentación - actas existentes
        $this->formActasExistentes = $vehiculo->actas()->get()->toArray();

        $this->showForm = true;
        $this->dispatch('abrir-modal-vehiculo');
    }

    // ── CERRAR FORMULARIO ──
    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
        $this->dispatch('cerrar-modal-vehiculo');
    }

    // ── HOOK AUTOMÁTICO DE LIVEWIRE: se ejecuta solo cuando termina de subirse
    //    el archivo temporal a $singleActaUpload (subido vía $wire.upload desde el JS) ──
    public function updatedSingleActaUpload()
    {
        if (! $this->singleActaUpload) {
            return;
        }

        $file = $this->singleActaUpload;
        $maxSize = 10485760; // 10MB individual

        // Nota: esta validación es una segunda barrera server-side. La primera
        // (y la que evita transferir archivos gigantes) va en el JS, antes de
        // llamar a $wire.upload — ver el input en la vista.
        if ($file->getSize() > $maxSize) {
            $this->addError('singleActaUpload', 'El archivo no puede superar 10MB individualmente.');
            $this->singleActaUpload = null;
            return;
        }

        $tamanoActual = 0;
        foreach ($this->queuedActaPaths as $acta) {
            $tamanoActual += (int) ($acta['tamano_bytes'] ?? 0);
        }
        foreach ($this->formActasExistentes as $acta) {
            $tamanoActual += (int) ($acta['tamano_bytes'] ?? 0);
        }

        if ($tamanoActual + $file->getSize() > 10485760) {
            $actualMB = round($tamanoActual / 1048576, 2);
            $nuevoMB = round($file->getSize() / 1048576, 2);
            $this->addError('singleActaUpload', "El total no puede superar 10MB. Actual: {$actualMB}MB, este archivo: {$nuevoMB}MB.");
            $this->singleActaUpload = null;
            return;
        }

        // Validar que la matrícula esté completa antes de crear la carpeta
        if (empty(trim($this->formMatricula))) {
            $this->addError('singleActaUpload', 'Completá la matrícula antes de adjuntar archivos.');
            $this->singleActaUpload = null;
            return;
        }

        // Guardar dentro de la carpeta de la matrícula (ej: actas/AA_123/)
        $carpeta = 'actas/' . $this->sanitizarMatriculaParaCarpeta($this->formMatricula);
        $path = $file->store($carpeta, 'public');

        // Guardamos path + nombre real + tamaño ya calculados acá, para no
        // tener que recalcularlos ni adivinarlos más adelante (ni en la
        // vista, ni al guardar el vehículo).
        $this->queuedActaPaths[] = [
            'path' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'tamano_bytes' => $file->getSize(),
        ];
        $this->singleActaUpload = null;
    }

    // ── ELIMINAR ACTA EN COLA (aún no guardada en BD) ──
    public function eliminarActaEnCola(int $index)
    {
        if (! isset($this->queuedActaPaths[$index])) {
            return;
        }

        $path = $this->queuedActaPaths[$index]['path'] ?? null;
        if ($path) {
            Storage::disk('public')->delete($path);
        }

        unset($this->queuedActaPaths[$index]);
        $this->queuedActaPaths = array_values($this->queuedActaPaths);
    }

    // ── ELIMINAR ACTA INDIVIDUAL ──
    public function eliminarActaExistente($actaId)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($this->formVehiculoId);
            $this->authorize('update', $vehiculo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $acta = VehiculoActa::find($actaId);
        if ($acta && $acta->vehiculo_id == $this->formVehiculoId) {
            Storage::disk('public')->delete($acta->path);
            $acta->delete();
        }

        $this->formActasExistentes = collect($this->formActasExistentes)
            ->reject(fn($a) => $a['id'] == $actaId)
            ->values()
            ->toArray();
    }

    // ── GUARDAR (create o update) ──
    public function guardar()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', Vehiculo::class);
            } else {
                $vehiculo = Vehiculo::findOrFail($this->formVehiculoId);
                $this->authorize('update', $vehiculo);
            }
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->validate(
            $this->reglasValidacion(),
            $this->mensajesValidacion()
        );

        // Validación manual: máximo 5 archivos
        $totalActas = count($this->formActasExistentes) + count($this->queuedActaPaths);
        if ($totalActas > 5) {
            $existentes = count($this->formActasExistentes);
            $nuevos = count($this->queuedActaPaths);
            $this->addError('queuedActaPaths', "Máximo 5 archivos por vehículo. Actualmente hay {$existentes}, intentás agregar {$nuevos}.");
            return;
        }

        // Validación manual: máximo 10MB en total
        $tamanoExistentesBytes = 0;
        foreach ($this->formActasExistentes as $acta) {
            $tamanoExistentesBytes += (int) ($acta['tamano_bytes'] ?? 0);
        }
        $tamanoNuevosBytes = 0;
        foreach ($this->queuedActaPaths as $acta) {
            $tamanoNuevosBytes += (int) ($acta['tamano_bytes'] ?? 0);
        }
        $limiteBytes = 10485760; // 10MB
        if ($tamanoExistentesBytes + $tamanoNuevosBytes > $limiteBytes) {
            $actualMB = round($tamanoExistentesBytes / 1048576, 2);
            $nuevosMB = round($tamanoNuevosBytes / 1048576, 2);
            $this->addError('queuedActaPaths', "El total de archivos no puede superar 10MB. Actual: {$actualMB}MB, intentás agregar {$nuevosMB}MB.");
            return;
        }

        $this->loading = true;

        try {
            $data = $this->datosValidados();

            if ($this->formTipo === 'create') {
                $vehiculo = Vehiculo::create($data);

                // Si la matrícula cambió después de subir archivos, mover los
                // archivos a la carpeta de la matrícula final (la que quedó en BD)
                $carpetaFinal = $vehiculo->carpetaActas();
                foreach ($this->queuedActaPaths as &$acta) {
                    $carpetaArchivo = dirname($acta['path']);
                    if ($carpetaArchivo !== $carpetaFinal) {
                        $nuevoPath = $carpetaFinal . '/' . basename($acta['path']);
                        Storage::disk('public')->move($acta['path'], $nuevoPath);
                        $acta['path'] = $nuevoPath;
                    }
                }
                unset($acta);

                // Crear registros VehiculoActa para los archivos ya subidos
                foreach ($this->queuedActaPaths as $acta) {
                    if (Storage::disk('public')->exists($acta['path'])) {
                        VehiculoActa::create([
                            'vehiculo_id' => $vehiculo->id,
                            'path' => $acta['path'],
                            'nombre_original' => $acta['nombre_original'],
                            'tamano_bytes' => $acta['tamano_bytes'],
                        ]);
                    }
                }
            } else {
                $vehiculo = Vehiculo::findOrFail($this->formVehiculoId);
                $matriculaVieja = $vehiculo->matricula;
                $matriculaNueva = $this->formMatricula;

                if ($matriculaVieja !== $matriculaNueva) {
                    $carpetaVieja = $vehiculo->carpetaActas();
                    $carpetaNueva = 'actas/' . $this->sanitizarMatriculaParaCarpeta($matriculaNueva);

                    if ($carpetaVieja !== $carpetaNueva) {
                        // Matricula cambió y la carpeta difiere: mover carpeta + actualizar paths en transacción
                        DB::transaction(function () use ($vehiculo, $carpetaVieja, $carpetaNueva, $data) {
                            // Mover carpeta de actas de la matrícula vieja a la nueva
                            if (Storage::disk('public')->exists($carpetaVieja)) {
                                if (Storage::disk('public')->exists($carpetaNueva)) {
                                    // Fusión de carpetas (no debería pasar por unique en matricula)
                                    $archivos = Storage::disk('public')->files($carpetaVieja);
                                    foreach ($archivos as $archivo) {
                                        $nombreArchivo = basename($archivo);
                                        $destino = $carpetaNueva . '/' . $nombreArchivo;
                                        if (Storage::disk('public')->exists($destino)) {
                                            $destino = $carpetaNueva . '/' . uniqid() . '_' . $nombreArchivo;
                                        }
                                        Storage::disk('public')->move($archivo, $destino);
                                    }
                                    Storage::disk('public')->deleteDirectory($carpetaVieja);
                                } else {
                                    Storage::disk('public')->move($carpetaVieja, $carpetaNueva);
                                }
                            }

                            // Actualizar vehículo con nueva matrícula
                            $vehiculo->update($data);

                            // Actualizar paths de VehiculoActa existentes
                            foreach ($vehiculo->actas as $acta) {
                                $nuevoPath = str_replace($carpetaVieja, $carpetaNueva, $acta->path);
                                $acta->update(['path' => $nuevoPath]);
                            }

                            // Crear registros VehiculoActa para los nuevos archivos en cola
                            foreach ($this->queuedActaPaths as $acta) {
                                if (Storage::disk('public')->exists($acta['path'])) {
                                    VehiculoActa::create([
                                        'vehiculo_id' => $vehiculo->id,
                                        'path' => $acta['path'],
                                        'nombre_original' => $acta['nombre_original'],
                                        'tamano_bytes' => $acta['tamano_bytes'],
                                    ]);
                                }
                            }
                        });
                    } else {
                        // Matricula cambió pero la carpeta sanitizada es la misma: sin mover archivos
                        DB::transaction(function () use ($vehiculo, $data) {
                            $vehiculo->update($data);
                            foreach ($this->queuedActaPaths as $acta) {
                                if (Storage::disk('public')->exists($acta['path'])) {
                                    VehiculoActa::create([
                                        'vehiculo_id' => $vehiculo->id,
                                        'path' => $acta['path'],
                                        'nombre_original' => $acta['nombre_original'],
                                        'tamano_bytes' => $acta['tamano_bytes'],
                                    ]);
                                }
                            }
                        });
                    }
                } else {
                    // Matricula no cambió: actualización simple
                    $vehiculo->update($data);
                    foreach ($this->queuedActaPaths as $acta) {
                        if (Storage::disk('public')->exists($acta['path'])) {
                            VehiculoActa::create([
                                'vehiculo_id' => $vehiculo->id,
                                'path' => $acta['path'],
                                'nombre_original' => $acta['nombre_original'],
                                'tamano_bytes' => $acta['tamano_bytes'],
                            ]);
                        }
                    }
                }
            }

            if ($this->formTipo === 'create') {
                $this->successMsg = 'Vehículo creado correctamente.';
            } else {
                $this->successMsg = 'Vehículo actualizado correctamente.';
            }

            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── DATOS VALIDADOS (array limpio) ──
    protected function datosValidados(): array
    {
        // Lógica especial de activo: forzar false si estado es rojo/negro
        $activo = $this->formActivo && !in_array($this->formEstado, ['rojo', 'negro']);

        return [
            'matricula' => $this->formMatricula,
            'marca' => $this->formMarca ?: null,
            'modelo' => $this->formModelo ?: null,
            'vehiculo' => $this->formVehiculo ?: null,
            'numero_chasis' => $this->formNumeroChasis ?: null,
            'numero_motor' => $this->formNumeroMotor ?: null,
            'ejes' => (int) $this->formEjes,
            'descripcion' => $this->formDescripcion ?: null,
            'estado' => $this->formEstado,
            'unidad_id' => $this->formUnidadId ?: null,
            'tipo_vehiculo_id' => $this->formTipoVehiculoId ?: null,
            'tipo_combustible_id' => $this->formTipoCombustibleId,
            'tipo_lubricante_id' => $this->formTipoLubricanteId ?: null,
            'tipo_rodado_id' => $this->formTipoRodadoId ?: null,
            'consumo_litros_por_km' => $this->formConsumoLitrosPorKm !== null ? (float) $this->formConsumoLitrosPorKm : null,
            'sin_cuentakilometros' => $this->formSinCuentakilometros,
            'activo' => $activo,
        ];
    }

    // ── ELIMINAR (confirmación + ejecución separada) ──
    public function confirmarEliminacion(int $vehiculoId)
    {
        $vehiculo = Vehiculo::findOrFail($vehiculoId);

        try {
            $this->authorize('delete', $vehiculo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        if ($vehiculo->salidas()->count() > 0) {
            $this->errorMsg = 'No se puede eliminar un vehículo con salidas asociadas.';
            return;
        }

        $this->confirmDeleteId = $vehiculoId;
    }

    public function ejecutarEliminacion()
    {
        $this->loading = true;
        try {
            $vehiculo = Vehiculo::findOrFail($this->confirmDeleteId);
            $vehiculo->delete();
            $this->successMsg = 'Vehículo eliminado correctamente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmDeleteId = null;
        }
    }

    // ── VER DETALLE (show) ──
    public function verDetalle(int $vehiculoId)
    {
        $vehiculo = Vehiculo::findOrFail($vehiculoId);

        try {
            $this->authorize('view', $vehiculo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->detalleVehiculoId = $vehiculoId;
        $this->detalleVehiculo = Vehiculo::with([
            'tipoVehiculo',
            'tipoCombustible',
            'tipoLubricante',
            'tipoRodado',
            'unidad',
            'salidas' => function ($query) {
                $query->with(['guardia', 'conductor'])->latest('id')->limit(10);
            },
            'actas',
        ])->findOrFail($vehiculoId);
        $this->showDetalle = true;
    }

    // ── CERRAR DETALLE ──
    public function cerrarDetalle()
    {
        $this->showDetalle = false;
        $this->detalleVehiculoId = null;
        $this->detalleVehiculo = null;
    }

    // ── ABRIR EDICIÓN DESDE DETALLE ──
    public function abrirEditarDesdeDetalle()
    {
        $this->cerrarDetalle();
        $this->abrirEditar($this->detalleVehiculoId);
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formVehiculoId = null;

        $this->formMatricula = '';
        $this->formMarca = '';
        $this->formModelo = '';
        $this->formVehiculo = '';
        $this->formNumeroChasis = '';
        $this->formNumeroMotor = '';
        $this->formEjes = 1;
        $this->formDescripcion = '';
        $this->formEstado = 'verde';

        $this->formUnidadId = null;
        $this->formTipoVehiculoId = null;
        $this->formTipoCombustibleId = null;
        $this->formTipoLubricanteId = null;
        $this->formTipoRodadoId = null;

        $this->formConsumoLitrosPorKm = null;
        $this->formSinCuentakilometros = false;
        $this->formActivo = true;

        $this->queuedActaPaths = [];
        $this->singleActaUpload = null;
        $this->formActasExistentes = [];
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $uniqueMatricula = $this->formTipo === 'create'
            ? 'unique:vehiculos,matricula'
            : 'unique:vehiculos,matricula,' . $this->formVehiculoId;
        $uniqueChasis = $this->formTipo === 'create'
            ? 'unique:vehiculos,numero_chasis'
            : 'unique:vehiculos,numero_chasis,' . $this->formVehiculoId;
        $uniqueMotor = $this->formTipo === 'create'
            ? 'unique:vehiculos,numero_motor'
            : 'unique:vehiculos,numero_motor,' . $this->formVehiculoId;

        return [
            // Datos generales
            'formMatricula' => 'required|string|max:20|' . $uniqueMatricula,
            'formMarca' => 'nullable|string|max:100',
            'formModelo' => 'nullable|string|max:100',
            'formVehiculo' => 'nullable|string|max:50',
            'formNumeroChasis' => 'nullable|string|max:50|' . $uniqueChasis,
            'formNumeroMotor' => 'nullable|string|max:50|' . $uniqueMotor,
            'formEjes' => 'required|integer|min:1|max:10',
            'formDescripcion' => 'nullable|string|max:255',
            'formEstado' => 'required|in:verde,amarillo,rojo,negro',

            // Catálogos
            'formUnidadId' => 'nullable|integer|exists:unidades,id',
            'formTipoVehiculoId' => 'nullable|integer|exists:tipos_vehiculo,id',
            'formTipoCombustibleId' => 'required|integer|exists:tipos_combustible,id',
            'formTipoLubricanteId' => 'nullable|integer|exists:tipos_lubricante,id',
            'formTipoRodadoId' => 'nullable|integer|exists:tipos_rodado,id',

            // Técnicos
            'formConsumoLitrosPorKm' => 'nullable|numeric|min:0|max:999.9999',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formMatricula.required' => 'La matrícula es obligatoria.',
            'formMatricula.unique' => 'Ya existe un vehículo con esa matrícula.',
            'formNumeroChasis.unique' => 'Ya existe un vehículo con ese número de chasis.',
            'formNumeroMotor.unique' => 'Ya existe un vehículo con ese número de motor.',
            'formEjes.required' => 'La cantidad de ejes es obligatoria.',
            'formEjes.min' => 'Debe tener al menos 1 eje.',
            'formEjes.max' => 'No puede tener más de 10 ejes.',
            'formEstado.required' => 'El estado es obligatorio.',
            'formEstado.in' => 'El estado debe ser: verde, amarillo, rojo o negro.',
            'formTipoCombustibleId.required' => 'El tipo de combustible es obligatorio.',
            'formConsumoLitrosPorKm.max' => 'El consumo no puede superar 999.9999.',
        ];
    }

    // ── REACTIVO: al cambiar búsqueda, resetear página ──
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

    // ── PAPELERA: alternar entre activos y papelera ──
    public function verPapelera()
    {
        $this->vistaPapelera = true;
        $this->search = '';
        $this->resetPage();
    }

    public function verActivos()
    {
        $this->vistaPapelera = false;
        $this->search = '';
        $this->resetPage();
    }

    // ── PAPELERA: restaurar vehículo ──
    public function restaurar(int $vehiculoId)
    {
        try {
            $vehiculo = Vehiculo::onlyTrashed()->findOrFail($vehiculoId);
            $this->authorize('restore', $vehiculo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $vehiculo->restore();
        $this->successMsg = 'Vehículo restaurado correctamente.';
    }

    // ── PAPELERA: confirmar eliminación permanente ──
    public function confirmarEliminacionPermanente(int $vehiculoId)
    {
        try {
            $vehiculo = Vehiculo::onlyTrashed()->findOrFail($vehiculoId);
            $this->authorize('forceDelete', $vehiculo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->confirmForceDeleteId = $vehiculoId;
    }

    // ── PAPELERA: ejecutar eliminación permanente (forceDelete) ──
    public function ejecutarEliminacionPermanente()
    {
        $this->loading = true;
        try {
            $vehiculo = Vehiculo::onlyTrashed()->findOrFail($this->confirmForceDeleteId);
            $vehiculo->forceDelete();
            $this->successMsg = 'Vehículo eliminado permanentemente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar permanentemente: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmForceDeleteId = null;
        }
    }

    // ── EXPORTAR EXCEL ──
    public function exportarExcel()
    {
        $this->authorize('viewAny', Vehiculo::class);

        $vehiculos = Vehiculo::with(['tipoVehiculo', 'unidad', 'tipoCombustible', 'tipoLubricante', 'tipoRodado'])
            ->orderBy('matricula')
            ->get()
            ->groupBy(fn($v) => $v->tipoVehiculo->nombre ?? 'Sin Tipo')
            ->sortKeys();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vehículos');

        $sheet->getColumnDimension('B')->setWidth(9.86);
        $sheet->getColumnDimension('C')->setWidth(11.29);
        $sheet->getColumnDimension('D')->setWidth(14.29);
        $sheet->getColumnDimension('E')->setWidth(10.29);
        $sheet->getColumnDimension('F')->setWidth(11.29);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(14.86);
        $sheet->getColumnDimension('I')->setWidth(9.57);
        $sheet->getColumnDimension('J')->setWidth(10.14);

        $headers = ['Unidad', 'Vehículo', 'Matricula', 'Marca', 'Modelo', 'Estado', 'Descripcion', 'Tipo de Comb.'];

        $titleStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'italic' => true],
        ];
        $headerStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'italic' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $dataStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 11],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $row = 3;

        foreach ($vehiculos as $tipoNombre => $items) {
            $sheet->setCellValue("B{$row}", $tipoNombre);
            $sheet->getStyle("B{$row}")->applyFromArray($titleStyle);
            $row++;

            $col = 'B';
            foreach ($headers as $h) {
                $sheet->setCellValue("{$col}{$row}", $h);
                $col++;
            }
            $sheet->mergeCells("I{$row}:J{$row}");
            $sheet->getStyle("B{$row}:J{$row}")->applyFromArray($headerStyle);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;

            foreach ($items as $v) {
                $sheet->setCellValue("B{$row}", $v->unidad->nombre ?? '-');
                $sheet->setCellValue("C{$row}", $v->vehiculo ?? '-');
                $sheet->setCellValue("D{$row}", $v->matricula);
                $sheet->setCellValue("E{$row}", $v->marca ?? '-');
                $sheet->setCellValue("F{$row}", $v->modelo ?? '-');
                $sheet->setCellValue("G{$row}", match ($v->estado) {
                    'verde' => 'V',
                    'amarillo' => 'A',
                    'rojo' => 'R',
                    'negro' => 'N',
                    default => '-',
                });
                $sheet->setCellValue("H{$row}", $v->descripcion ?? '-');
                $sheet->setCellValue("I{$row}", $v->tipoCombustible->nombre ?? '-');
                $sheet->mergeCells("I{$row}:J{$row}");
                $sheet->getStyle("B{$row}:J{$row}")->applyFromArray($dataStyle);
                $row++;
            }

            $row += 2;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'vehiculos_' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ── SANITIZACIÓN DE MATRÍCULA PARA NOMBRES DE CARPETA ──
    protected function sanitizarMatriculaParaCarpeta(string $matricula): string
    {
        return trim(
            preg_replace('/_+/', '_', strtoupper(preg_replace('/[^A-Z0-9]/', '_', $matricula))),
            '_'
        );
    }

    // ── EVENT LISTENERS: refrescar catálogos cuando se actualizan desde modales ──
    #[\Livewire\Attributes\On('combustible-actualizado')]
    #[\Livewire\Attributes\On('lubricante-actualizado')]
    #[\Livewire\Attributes\On('rodado-actualizado')]
    public function refrescarCatalogos()
    {
        unset($this->catalogos);
    }

    // ── RENDER ──
    public function render()
    {
        return view('livewire.vehiculos.index', [
            'vehiculos' => $this->vehiculos(),
            'catalogos' => $this->catalogos(),
        ]);
    }
}