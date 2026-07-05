<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log_News extends Model
{
    //
    protected $table = 'log__news';

    protected $fillable = [
        'news_id',
        'user_id',
        'action',
        'data_before',
        'data_after',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'data_before' => 'array',
            'data_after'  => 'array',
        ];
    }

    const ACCIONES = ['Creado', 'Cerrado', 'Modificado', 'Eliminado'];

    // Relationships
    public function novedad(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
