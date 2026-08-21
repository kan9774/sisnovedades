<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TipoApoyo extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'tipos_apoyo';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Tipo Apoyo');
    }

    protected $fillable = ['nombre', 'color'];

    public function apoyos(): HasMany
    {
        return $this->hasMany(Apoyo::class, 'tipo_id');
    }
}
