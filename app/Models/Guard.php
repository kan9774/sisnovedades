<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Guard extends Model
{

    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Guardias'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }



    protected $fillable = [
        'date',
        'captain_id',
        'oficer_id',
        'status',
        'closed_at',
        'notes'
    ];
    protected $table = 'guards';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'closed_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    //Relationships
    public function capitan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captain_id');
    }
    public function oficial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oficer_id');
    }
    public function novedades(): HasMany
    {
        return $this->hasMany(News::class, 'guard_id');
    }
    public function escribiente(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardia_escribientes', 'guardia_id',  'escribiente_id')
            ->withPivot('hora_inicio', 'hora_fin');
    }

    public function novedadesPersonal(): HasMany
    {
        return $this->hasMany(NovedadPersonal::class, 'guard_id');
    }

    public function novedadesRancho(): HasMany
    {
        return $this->hasMany(NovedadRancho::class, 'guard_id');
    }
    public function ranchoMenu(): HasOne
    {
        return $this->hasOne(RanchoMenu::class, 'guard_id');
    }

    public function pdfRecipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guard_pdf_recipient')
            ->withPivot('downloaded_or_read_at')
            ->withTimestamps();
    }


    //Scopes
    public function scopeAbierta($query)
    {
        return $query->where('status', 'open');
    }
    public function scopeCerrada($query)
    {
        return $query->where('status', 'closed');
    }
    public function scopeHoy($query)
    {
        return $query->whereDate('date', today());
    }
    public function scopeWithTrashed($query)
    {
        return $query->withTrashed();
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->onlyTrashed();
    }
    public function salidasVehiculos(): HasMany
    {
        return $this->hasMany(SalidaVehiculo::class,  'guardia_id');
    }

    //Helpers
    public function isAbierta(): bool
    {
        return $this->status === 'open';
    }

    public function esMiembro(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->captain_id === $user->id
            || $this->oficer_id === $user->id
            || $this->escribiente->contains('id', $user->id);
    }
    public function isAbiertaNoDelete(): bool
    {
        return $this->status === 'open' && $this->deleted_at === null;
    }

    public function isCerrada(): bool
    {
        return $this->status === 'closed' && $this->deleted_at === null;
    }

    public function isEliminada(): bool
    {
        return $this->deleted_at !== null;
    }
}
