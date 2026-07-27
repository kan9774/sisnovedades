<?php

namespace App\Http\Controllers\Admin\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Entrega;

class EntregaController extends Controller
{
    public function comprobante(Entrega $entrega)
    {
        $this->authorize('view', $entrega);

        $entrega->load([
            'ubicacionOrigen',
            'ubicacionDestino',
            'usuario',
            'movimientos.item',
            'movimientos.itemUnidad',
        ]);

        return view('admin.inventario.comprobante-entrega', [
            'entrega' => $entrega,
        ]);
    }
}