<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaDocumento extends Model
{
    protected $fillable = ['nombre', 'slug', 'descripcion'];
    protected $table = 'categorias_documentos';

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
