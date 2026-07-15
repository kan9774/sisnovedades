<?php

namespace App\Livewire\Catalogos;

use App\Models\TipoCombustible;

class TiposCombustibleModal extends CatalogoSimpleModal
{
    protected function modelClass(): string
    {
        return TipoCombustible::class;
    }
    protected function eventoActualizado(): string
    {
        return 'combustible-actualizado';
    }
    public function titulo(): string
    {
        return 'Tipos de Combustible';
    }

    public function render()
    {
        return view('livewire.catalogos.catalogo-simple-modal');
    }
}
