<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\Movimiento;
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
        ?string $referencia = null
    ): Movimiento {
        $this->asegurarPorCantidad($item);
        $this->asegurarCantidadPositiva($cantidad);

        return DB::transaction(function () use ($item, $destino, $cantidad, $usuario, $motivo, $referencia) {
            $this->incrementarStock($item, $destino, $cantidad);

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
        ?string $motivo = null
    ): Movimiento {
        $this->asegurarPorCantidad($item);
        $this->asegurarCantidadPositiva($cantidad);

        if ($origen->is($destino)) {
            throw new InvalidArgumentException('El origen y el destino de la transferencia no pueden ser la misma ubicación.');
        }

        return DB::transaction(function () use ($item, $origen, $destino, $cantidad, $usuario, $motivo) {
            $this->decrementarStock($item, $origen, $cantidad);
            $this->incrementarStock($item, $destino, $cantidad);

            return Movimiento::create([
                'item_id' => $item->id,
                'tipo' => 'transferencia',
                'cantidad' => $cantidad,
                'ubicacion_origen_id' => $origen->id,
                'ubicacion_destino_id' => $destino->id,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
            ]);
        });
    }

    /**
     * Ajusta el stock de un item en una ubicación al valor real contado
     * (ej: tras un conteo físico), registrando la diferencia como movimiento.
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

    /*
    |--------------------------------------------------------------------
    | Items individuales (con seguimiento por unidad: PC, radio, silla...)
    |--------------------------------------------------------------------
    */

    public function asignarUnidad(
        ItemUnidad $unidad,
        Ubicacion $destino,
        User $usuario,
        ?string $motivo = null
    ): Movimiento {
        $this->asegurarIndividual($unidad->item);

        if ($unidad->estado === 'baja') {
            throw new InvalidArgumentException('No se puede reasignar una unidad que ya fue dada de baja.');
        }

        return DB::transaction(function () use ($unidad, $destino, $usuario, $motivo) {
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
            ]);
        });
    }

    public function marcarEnReparacion(ItemUnidad $unidad, User $usuario, ?string $motivo = null): Movimiento
    {
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
        ?string $motivo = null
    ): ItemUnidad {
        $this->asegurarIndividual($item);

        return DB::transaction(function () use ($item, $ubicacion, $usuario, $numeroSerie, $motivo) {
            $unidad = ItemUnidad::create([
                'item_id' => $item->id,
                'numero_serie' => $numeroSerie,
                'estado' => 'disponible',
                'ubicacion_actual_id' => $ubicacion->id,
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

    /*
    |--------------------------------------------------------------------
    | Helpers internos
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
}