<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RanchoMenu extends Model
{
    protected $table = 'rancho_menus';

    protected $fillable = ['guard_id', 'menu_desayuno', 'menu_almuerzo', 'menu_merienda', 'menu_cena'];

    public function guardia(): BelongsTo
    {
        return $this->belongsTo(Guard::class, 'guard_id');
    }
}