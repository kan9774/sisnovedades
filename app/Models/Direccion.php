<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direccion extends Model
{
    use SoftDeletes;

    protected $table = 'direcciones';
    protected $fillable = [
        'user_id',
        'tipo',
        'departamento_id',
        'localidad',
        'calle',
        'numero',
        'esquina',
        'apartamento',
        'barrio',
        'codigo_postal',
        'referencia',
        'es_principal',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Direccion $direccion) {
            if ($direccion->es_principal) {
                static::where('user_id', $direccion->user_id)
                    ->where('id', '!=', $direccion->id)
                    ->update(['es_principal' => false]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function getDireccionCompletaAttribute(): string
    {
        $partes = array_filter([
            $this->calle,
            $this->numero,
            $this->esquina ? "esq. {$this->esquina}" : null,
            $this->apartamento ? "apto. {$this->apartamento}" : null,
        ]);

        $linea1 = implode(' ', $partes);
        $linea2 = implode(', ', array_filter([$this->barrio, $this->localidad, $this->departamento?->nombre]));

        return trim("{$linea1}, {$linea2}", ', ');
    }
}