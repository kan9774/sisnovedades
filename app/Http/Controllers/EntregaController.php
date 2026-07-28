<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Support\EntregaPdfGenerator;

class EntregaController extends Controller
{
    public function comprobante(Entrega $entrega)
    {
        $this->authorize('view', $entrega);

        return EntregaPdfGenerator::generar($entrega)
            ->stream(EntregaPdfGenerator::nombreArchivo($entrega));
    }
}