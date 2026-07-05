<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Organismo extends Model
{
    //
    protected $fillable = ['name'];

    public function novedades(): HasMany
    {
        return $this->hasMany(News::class, 'organismo_id');
    }
}
