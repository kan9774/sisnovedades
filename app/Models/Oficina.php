<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Oficina extends Model
{
    use LogsActivity;

    protected $table = 'oficinas';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('oficina');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function novedades(): HasMany
    {
        return $this->hasMany(News::class, 'office_id');
    }
}