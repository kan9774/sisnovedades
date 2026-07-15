<?php   
// app/Livewire/Catalogos/TiposCombustibleModal.php
namespace App\Livewire\Catalogos;

use App\Models\TipoLubricante;

class TiposLubricanteModal extends CatalogoSimpleModal
{
    protected function modelClass(): string { return TipoLubricante::class; }
    protected function eventoActualizado(): string { return 'lubricante-actualizado'; }
    public function titulo(): string { return 'Tipos de Combustible'; }

    public function render()
    {
        return view('livewire.catalogos.catalogo-simple-modal');
    }
}