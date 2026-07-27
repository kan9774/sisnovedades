<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';
    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
    ];

    public function itemUnidades(): HasMany
    {
        return $this->hasMany(ItemUnidad::class);
    }

    public function lotesStock(): HasMany
    {
        return $this->hasMany(LoteStock::class);
    }
}
