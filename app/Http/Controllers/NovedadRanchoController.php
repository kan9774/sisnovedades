<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaOperacionGuardia;
use App\Models\Guard;
use App\Models\NovedadRancho;
use App\Models\RanchoMenu;
use Illuminate\Http\Request;

class NovedadRanchoController extends Controller
{
    use AutorizaOperacionGuardia;

    public function update(Request $request, Guard $guardia)
    {
        $this->autorizarOperacion($guardia);

        $data = $request->validate([
            'unidades'            => 'required|array',
            'unidades.*.desayuno' => 'nullable|integer|min:0',
            'unidades.*.almuerzo' => 'nullable|integer|min:0',
            'unidades.*.merienda' => 'nullable|integer|min:0',
            'unidades.*.cena'     => 'nullable|integer|min:0',
            'menu_desayuno'       => 'nullable|string|max:255',
            'menu_almuerzo'       => 'nullable|string|max:255',
            'menu_merienda'       => 'nullable|string|max:255',
            'menu_cena'           => 'nullable|string|max:255',
        ]);

        foreach ($data['unidades'] as $unidadId => $valores) {
            $tieneDatos = collect($valores)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();

            if ($tieneDatos) {
                NovedadRancho::updateOrCreate(
                    ['guard_id' => $guardia->id, 'unidad_id' => $unidadId],
                    $valores
                );
            }
        }

        $menus = collect($data)->only(['menu_desayuno', 'menu_almuerzo', 'menu_merienda', 'menu_cena'])
            ->filter(fn($v) => filled($v));

        if ($menus->isNotEmpty()) {
            RanchoMenu::updateOrCreate(['guard_id' => $guardia->id], $menus->toArray());
        }

        return back()->with('success', 'Novedades de rancho guardadas.');
    }
}
