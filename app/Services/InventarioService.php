<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Models\Entrega;
use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\LoteStock;
use App\Models\Movimiento;
use App\Models\Proveedor;
use App\Models\Stock;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventarioService
{
    /*
    |--------------------------------------------------------------------
    | Items por cantidad (consumibles, insumos, etc.)
    |--------------------------------------------------------------------
    */

    public function registrarEntrada(
        Item $item,
        Ubicacion $destino,
        int $cantidad,
        User $usuario,
        ?string $motivo = null,
        ?string $referencia = null,
        ?Proveedor $proveedor = null,
        ?string $fechaRecibido = null,
    ): Movimiento {
        $this->asegurarPorCantidad($item);
        $this->asegurarCantidadPositiva($cantidad);
        $this->asegurarEsDepositoGeneral($destino, 'registrar una entrada de stock');
        $this->asegurarPorCantidad($item);
        $this->asegurarCantidadPositiva($cantidad);

        return DB::transaction(function () use ($item, $destino, $cantidad, $usuario, $motivo, $referencia, $proveedor, $fechaRecibido) {
            $this->incrementarStock($item, $destino, $cantidad);

            $this->acreditarLote($item, $destino, [
                'proveedor_id' => $proveedor?->id,
                'fecha_recibido' => $fechaRecibido ?? now()->toDateString(),
                'cantidad' => $cantidad,
            ], $referencia);

            return Movimiento::create([
                'item_id' => $item->id,
                'tipo' => 'entrada',
                'cantidad' => $cantidad,
                'ubicacion_destino_id' => $destino->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
                'referencia' => $referencia,
            ]);
        });
    }

    public function registrarSalida(
        Item $item,
        Ubicacion $origen,
        int $cantidad,
        User $usuario,
        ?string $motivo = null,
        ?string $referencia = null
    ): Movimiento {
        $this->asegurarPorCantidad($item);
        $this->asegurarCantidadPositiva($cantidad);

        return DB::transaction(function () use ($item, $origen, $cantidad, $usuario, $motivo, $referencia) {
            $this->decrementarStock($item, $origen, $cantidad);
            $this->consumirLotesFefo($item, $origen, $cantidad);

            return Movimiento::create([
                'item_id' => $item->id,
                'tipo' => 'salida',
                'cantidad' => $cantidad,
                'ubicacion_origen_id' => $origen->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
                'referencia' => $referencia,
            ]);
        });
    }

    public function registrarTransferencia(
        Item $item,
        Ubicacion $origen,
        Ubicacion $destino,
        int $cantidad,
        User $usuario,
        ?string $motivo = null,
        ?int $entregaId = null,
    ): Movimiento {
        $this->asegurarPorCantidad($item);
        $this->asegurarCantidadPositiva($cantidad);

        if ($origen->is($destino)) {
            throw new InvalidArgumentException('El origen y el destino de la transferencia no pueden ser la misma ubicación.');
        }

        return DB::transaction(function () use ($item, $origen, $destino, $cantidad, $usuario, $motivo, $entregaId) {
            $this->decrementarStock($item, $origen, $cantidad);
            $this->incrementarStock($item, $destino, $cantidad);

            // Las partidas consumidas en origen (con su fecha_recibido y
            // proveedor originales) se re-acreditan en destino, así la
            // fecha de vencimiento del lote viaja con la mercadería en
            // vez de "resetearse" a hoy.
            $partidas = $this->consumirLotesFefo($item, $origen, $cantidad);
            foreach ($partidas as $partida) {
                $this->acreditarLote($item, $destino, $partida);
            }

            return Movimiento::create([
                'item_id' => $item->id,
                'tipo' => 'transferencia',
                'cantidad' => $cantidad,
                'ubicacion_origen_id' => $origen->id,
                'ubicacion_destino_id' => $destino->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
                'entrega_id' => $entregaId,
            ]);
        });
    }

    /**
     * Ajusta el stock de un item en una ubicación al valor real contado
     * (ej: tras un conteo físico), registrando la diferencia como movimiento.
     *
     * Faltante (cantidadReal < stock actual): se descuenta de los lotes
     * existentes por FEFO, igual que una salida.
     *
     * Sobrante (cantidadReal > stock actual): se registra como un lote
     * nuevo sin proveedor y con fecha de hoy, ya que no se conoce su
     * origen real — queda marcado como "Ajuste de inventario" para
     * distinguirlo de una entrada normal.
     */
    public function registrarAjuste(
        Item $item,
        Ubicacion $ubicacion,
        int $cantidadReal,
        User $usuario,
        ?string $motivo = null
    ): Movimiento {
        $this->asegurarPorCantidad($item);

        if ($cantidadReal < 0) {
            throw new InvalidArgumentException('La cantidad real no puede ser negativa.');
        }

        return DB::transaction(function () use ($item, $ubicacion, $cantidadReal, $usuario, $motivo) {
            $stock = $this->obtenerOCrearStockConLock($item, $ubicacion);
            $diferencia = $cantidadReal - $stock->cantidad;

            $stock->update(['cantidad' => $cantidadReal]);

            if ($diferencia < 0) {
                $this->consumirLotesFefo($item, $ubicacion, abs($diferencia));
            } elseif ($diferencia > 0) {
                $this->acreditarLote($item, $ubicacion, [
                    'proveedor_id' => null,
                    'fecha_recibido' => now()->toDateString(),
                    'cantidad' => $diferencia,
                ], 'Ajuste de inventario');
            }

            return Movimiento::create([
                'item_id' => $item->id,
                'tipo' => 'ajuste',
                'cantidad' => $diferencia,
                'ubicacion_destino_id' => $ubicacion->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
            ]);
        });
    }

    public function stockActual(Item $item, Ubicacion $ubicacion): int
    {
        $this->asegurarPorCantidad($item);

        return Stock::where('item_id', $item->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->value('cantidad') ?? 0;
    }

    /**
     * Lotes con stock disponible de un item en una ubicación, ordenados
     * del que vence antes al que vence después (FEFO). Útil para mostrar
     * el detalle de vencimientos en pantalla.
     */
    public function lotesDisponibles(Item $item, Ubicacion $ubicacion)
    {
        return LoteStock::where('item_id', $item->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_recibido')
            ->orderBy('id')
            ->get();
    }

    /*
    |--------------------------------------------------------------------
    | Items individuales (con seguimiento por unidad: PC, radio, silla...)
    |--------------------------------------------------------------------
    */

    public function asignarUnidad(
        ItemUnidad $unidad,
        Ubicacion $destino,
        User $usuario,
        ?string $motivo = null,
        ?int $entregaId = null,
    ): Movimiento {
        $this->asegurarIndividual($unidad->item);

        if ($unidad->estado === 'baja') {
            throw new InvalidArgumentException('No se puede reasignar una unidad que ya fue dada de baja.');
        }

        return DB::transaction(function () use ($unidad, $destino, $usuario, $motivo, $entregaId) {
            $origenId = $unidad->ubicacion_actual_id;

            $unidad->update([
                'ubicacion_actual_id' => $destino->id,
                'estado' => 'asignado',
            ]);

            return Movimiento::create([
                'item_id' => $unidad->item_id,
                'item_unidad_id' => $unidad->id,
                'tipo' => 'transferencia',
                'ubicacion_origen_id' => $origenId,
                'ubicacion_destino_id' => $destino->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
                'entrega_id' => $entregaId,
            ]);
        });
    }

    public function marcarEnReparacion(ItemUnidad $unidad, User $usuario, ?string $motivo = null): Movimiento
    {
        $ubicacionActual = Ubicacion::findOrFail($unidad->ubicacion_actual_id);
        $this->asegurarEsDepositoGeneral($ubicacionActual, 'enviar a reparación');

        return DB::transaction(function () use ($unidad, $usuario, $motivo) {
            $unidad->update(['estado' => 'en_reparacion']);

            return Movimiento::create([
                'item_id' => $unidad->item_id,
                'item_unidad_id' => $unidad->id,
                'tipo' => 'ajuste',
                'ubicacion_origen_id' => $unidad->ubicacion_actual_id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo ?? 'Enviado a reparación',
            ]);
        });
    }

    public function darDeAltaUnidad(
        Item $item,
        Ubicacion $ubicacion,
        User $usuario,
        ?string $numeroSerie = null,
        ?string $motivo = null,
        ?Proveedor $proveedor = null,
        ?string $fechaRecibido = null,
    ): ItemUnidad {
        $this->asegurarIndividual($item);
        $this->asegurarEsDepositoGeneral($ubicacion, 'dar de alta una unidad');
        $this->asegurarIndividual($item);

        return DB::transaction(function () use ($item, $ubicacion, $usuario, $numeroSerie, $motivo, $proveedor, $fechaRecibido) {
            $unidad = ItemUnidad::create([
                'item_id' => $item->id,
                'numero_serie' => $numeroSerie,
                'estado' => 'disponible',
                'ubicacion_actual_id' => $ubicacion->id,
                'proveedor_id' => $proveedor?->id,
                'fecha_recibido' => $fechaRecibido,
                'fecha_alta' => now(),
            ]);

            Movimiento::create([
                'item_id' => $item->id,
                'item_unidad_id' => $unidad->id,
                'tipo' => 'entrada',
                'ubicacion_destino_id' => $ubicacion->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
            ]);

            return $unidad;
        });
    }

    public function darDeBajaUnidad(ItemUnidad $unidad, User $usuario, string $motivo): Movimiento
    {
        if ($unidad->estado === 'baja') {
            throw new InvalidArgumentException('Esta unidad ya está dada de baja.');
        }

        $ubicacionActual = Ubicacion::findOrFail($unidad->ubicacion_actual_id);
        $this->asegurarEsDepositoGeneral($ubicacionActual, 'dar de baja una unidad');

        return DB::transaction(function () use ($unidad, $usuario, $motivo) {
            $origenId = $unidad->ubicacion_actual_id;

            $unidad->update([
                'estado' => 'baja',
                'fecha_baja' => now(),
            ]);

            return Movimiento::create([
                'item_id' => $unidad->item_id,
                'item_unidad_id' => $unidad->id,
                'tipo' => 'baja',
                'ubicacion_origen_id' => $origenId,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
            ]);
        });
    }

    public function volverDeReparacion(ItemUnidad $unidad, User $usuario, ?string $motivo = null): Movimiento
    {
        if ($unidad->estado !== 'en_reparacion') {
            throw new InvalidArgumentException('Esta unidad no está en reparación.');
        }

        return DB::transaction(function () use ($unidad, $usuario, $motivo) {
            $unidad->update(['estado' => 'disponible']);

            return Movimiento::create([
                'item_id' => $unidad->item_id,
                'item_unidad_id' => $unidad->id,
                'tipo' => 'ajuste',
                'ubicacion_origen_id' => $unidad->ubicacion_actual_id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo ?? 'Vuelta de reparación',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------
    | Entregas y devoluciones (carrito multi-ítem)
    |--------------------------------------------------------------------
    */

    /**
     * Procesa una entrega o devolución con varias líneas (mezclando
     * ítems por cantidad e individuales) como una sola operación
     * atómica, agrupada bajo un registro de Entrega para el comprobante.
     *
     * $lineas: array de arrays, cada uno con:
     *   - 'item_id' (int, requerido)
     *   - 'cantidad' (int, requerido si el item es por cantidad)
     *   - 'item_unidad_id' (int, requerido si el item es individual)
     */
    public function registrarEntregaCarrito(
        string $tipo, // 'entrega' | 'devolucion'
        Ubicacion $origen,
        Ubicacion $destino,
        User $usuario,
        array $lineas,
        ?string $motivo = null,
    ): Entrega {
        if (! in_array($tipo, ['entrega', 'devolucion'], true)) {
            throw new InvalidArgumentException('Tipo de entrega inválido.');
        }

        if ($origen->is($destino)) {
            throw new InvalidArgumentException('El origen y el destino no pueden ser la misma ubicación.');
        }

        if (empty($lineas)) {
            throw new InvalidArgumentException('La entrega debe tener al menos un ítem.');
        }

        return DB::transaction(function () use ($tipo, $origen, $destino, $usuario, $lineas, $motivo) {
            $entrega = Entrega::create([
                'tipo' => $tipo,
                'ubicacion_origen_id' => $origen->id,
                'ubicacion_destino_id' => $destino->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
            ]);

            foreach ($lineas as $linea) {
                $item = Item::findOrFail($linea['item_id']);

                if ($item->esPorCantidad()) {
                    $cantidad = (int) ($linea['cantidad'] ?? 0);

                    if ($cantidad <= 0) {
                        throw new InvalidArgumentException("Indicá una cantidad válida para \"{$item->nombre}\".");
                    }

                    $this->registrarTransferencia($item, $origen, $destino, $cantidad, $usuario, $motivo, $entrega->id);
                } else {
                    $unidad = ItemUnidad::findOrFail($linea['item_unidad_id'] ?? null);

                    if ($unidad->item_id !== $item->id) {
                        throw new InvalidArgumentException('La unidad seleccionada no corresponde al ítem indicado.');
                    }

                    if ($unidad->ubicacion_actual_id !== $origen->id) {
                        throw new InvalidArgumentException(
                            "La unidad \"{$unidad->numero_serie}\" ya no está en \"{$origen->nombre}\" (puede haber sido movida por otra operación)."
                        );
                    }

                    $this->asignarUnidad($unidad, $destino, $usuario, $motivo, $entrega->id);
                }
            }

            return $entrega;
        });
    }

    /*
    |--------------------------------------------------------------------
    | Helpers internos: stock agregado
    |--------------------------------------------------------------------
    */

    private function incrementarStock(Item $item, Ubicacion $ubicacion, int $cantidad): void
    {
        $stock = $this->obtenerOCrearStockConLock($item, $ubicacion);
        $stock->increment('cantidad', $cantidad);
    }

    private function decrementarStock(Item $item, Ubicacion $ubicacion, int $cantidad): void
    {
        $stock = $this->obtenerOCrearStockConLock($item, $ubicacion);

        if ($stock->cantidad < $cantidad) {
            throw StockInsuficienteException::paraItem($item->nombre, $stock->cantidad, $cantidad);
        }

        $stock->decrement('cantidad', $cantidad);
    }

    /**
     * Obtiene (o crea) la fila de stock para item+ubicación con lock
     * pesimista, para evitar condiciones de carrera si dos movimientos
     * del mismo item/ubicación se registran en simultáneo.
     * Debe llamarse siempre dentro de una transacción.
     */
    private function obtenerOCrearStockConLock(Item $item, Ubicacion $ubicacion): Stock
    {
        $stock = Stock::where('item_id', $item->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        // firstOrCreate no es 100% atómico ante creación concurrente;
        // si dos requests crean la misma fila al mismo tiempo, la
        // constraint unique(item_id, ubicacion_id) hace fallar a una
        // de las dos, que simplemente debe reintentar la operación.
        return Stock::create([
            'item_id' => $item->id,
            'ubicacion_id' => $ubicacion->id,
            'cantidad' => 0,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | Helpers internos: lotes (FEFO)
    |--------------------------------------------------------------------
    */

    /**
     * Descuenta `$cantidad` de los lotes disponibles de un item en una
     * ubicación, empezando por el que vence antes (FEFO). Como la vida
     * útil es una propiedad del item (no del lote), dentro de un mismo
     * item "vence antes" es siempre equivalente a "se recibió antes", así
     * que alcanza con ordenar por fecha_recibido.
     *
     * Devuelve el detalle de las partidas realmente consumidas
     * (fecha_recibido, proveedor_id, cantidad), útil para transferencias
     * donde ese detalle tiene que viajar al destino.
     *
     * Debe llamarse siempre dentro de una transacción.
     *
     * @return array<int, array{fecha_recibido: string, proveedor_id: ?int, cantidad: int}>
     */
    private function consumirLotesFefo(Item $item, Ubicacion $ubicacion, int $cantidad): array
    {
        $restante = $cantidad;
        $consumido = [];

        $lotes = LoteStock::where('item_id', $item->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_recibido')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($restante <= 0) {
                break;
            }

            $tomar = min($lote->cantidad_actual, $restante);
            $lote->decrement('cantidad_actual', $tomar);
            $restante -= $tomar;

            $consumido[] = [
                'fecha_recibido' => $lote->fecha_recibido->toDateString(),
                'proveedor_id' => $lote->proveedor_id,
                'cantidad' => $tomar,
            ];
        }

        if ($restante > 0) {
            // No debería pasar si el stock agregado y los lotes están
            // sincronizados; lo dejamos como salvaguarda ante una
            // desincronización entre `stocks` y `lotes_stock`.
            throw new InvalidArgumentException(
                "Los lotes registrados de \"{$item->nombre}\" no alcanzan a cubrir la cantidad solicitada "
                    . "(faltan {$restante} unidades sin lote asociado)."
            );
        }

        return $consumido;
    }

    /**
     * Acredita una cantidad a un lote existente (mismo item+ubicación+
     * proveedor+fecha_recibido) o crea uno nuevo si no existe.
     *
     * @param array{proveedor_id: ?int, fecha_recibido: string, cantidad: int} $partida
     */
    private function acreditarLote(Item $item, Ubicacion $ubicacion, array $partida, ?string $referencia = null): void
    {
        $lote = LoteStock::where('item_id', $item->id)
            ->where('ubicacion_id', $ubicacion->id)
            ->where('proveedor_id', $partida['proveedor_id'])
            ->where('fecha_recibido', $partida['fecha_recibido'])
            ->lockForUpdate()
            ->first();

        if ($lote) {
            $lote->increment('cantidad_actual', $partida['cantidad']);
            $lote->increment('cantidad_inicial', $partida['cantidad']);
            return;
        }

        LoteStock::create([
            'item_id' => $item->id,
            'ubicacion_id' => $ubicacion->id,
            'proveedor_id' => $partida['proveedor_id'],
            'fecha_recibido' => $partida['fecha_recibido'],
            'cantidad_inicial' => $partida['cantidad'],
            'cantidad_actual' => $partida['cantidad'],
            'referencia' => $referencia,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | Validaciones internas
    |--------------------------------------------------------------------
    */

    private function asegurarPorCantidad(Item $item): void
    {
        if (! $item->esPorCantidad()) {
            throw new InvalidArgumentException(
                "El item \"{$item->nombre}\" es de seguimiento individual, no por cantidad."
            );
        }
    }

    private function asegurarIndividual(Item $item): void
    {
        if (! $item->esIndividual()) {
            throw new InvalidArgumentException(
                "El item \"{$item->nombre}\" es de seguimiento por cantidad, no individual."
            );
        }
    }

    private function asegurarCantidadPositiva(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser mayor a cero.');
        }
    }
    private function depositoGeneral(): Ubicacion
    {
        try {
            return Ubicacion::general();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            throw new InvalidArgumentException(
                'No existe un Depósito General configurado. Es la ubicación central del sistema: sin él no se puede dar de alta stock ni unidades.'
            );
        }
    }

    private function asegurarEsDepositoGeneral(Ubicacion $ubicacion, string $accion): void
    {
        if (! $ubicacion->is($this->depositoGeneral())) {
            throw new InvalidArgumentException(
                "Solo se puede {$accion} desde el Depósito General. Esta unidad está en \"{$ubicacion->nombre}\"; devolvela al Depósito General primero."
            );
        }
    }
}
