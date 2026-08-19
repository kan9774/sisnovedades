<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovedadPersonal extends Model
{
    protected $table = 'novedades_personal';

    protected $fillable = ['guard_id', 'user_id', 'hora', 'fecha', 'tipo', 'texto'];

    protected function casts(): array
    {
        return ['hora' => 'datetime:H:i', 'fecha' => 'date'];
    }

    public function guardia(): BelongsTo
    {
        return $this->belongsTo(Guard::class, 'guard_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}