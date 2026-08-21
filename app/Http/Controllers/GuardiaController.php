<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Support\GuardiaPdfGenerator;
use Illuminate\Http\Request;

class GuardiaController extends Controller
{
    public function show(Guard $guardia)
    {
        $this->authorize('view', $guardia);

        $guardia->load(['capitan.grado', 'oficial.grado', 'escribiente.grado', 'ranchoMenu']);

        $unidadesActivas = \App\Models\Unidad::curadasPara('guardias_rancho')->get();
        $rancho = $guardia->novedadesRancho->keyBy('unidad_id');

        return view('admin.guardias.show', compact(
            'guardia',
            'unidadesActivas',
            'rancho'
        ));
    }

    public function Hoy()
    {
        $guardia = Guard::Hoy()->first();
        if (!$guardia) {
            return redirect()->route('admin.guardias.index')->with('error', 'No hay guardia para hoy');
        }
        return redirect()->route('admin.guardias.show', $guardia);
    }

    public function pdf(Guard $guardia, Request $request)
    {
        $firmaSeleccionada = collect($request->input('firma', []))
            ->intersect(['capitan', 'oficial'])
            ->values()
            ->all();

        $pdf = app(GuardiaPdfGenerator::class)->generar($guardia, firmaSeleccionada: $firmaSeleccionada);

        return $pdf->stream("guardia-{$guardia->id}.pdf");
    }
}
