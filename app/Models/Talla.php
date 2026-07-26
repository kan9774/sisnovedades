<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Talla extends Model
{
    use HasFactory;

    protected $fillable = [
        'valor',
        'sistema',
        'orden',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}