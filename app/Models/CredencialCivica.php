<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CredencialCivica extends Model
{
    use HasFactory;

    protected $table = 'credenciales_civicas'; // nombre en español, explícito por las dudas

    protected $fillable = [
        'user_id',
        'departamento_id',
        'serie',
        'numero',
        'fecha_desde',
        'fecha_hasta',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    /**
     * Al crear una nueva credencial, cierra la anterior vigente (si existe)
     * y sincroniza el caché en users. Mismo patrón que Pase::created().
     */
    protected static function booted(): void
    {
        static::created(function (CredencialCivica $credencial) {
            // Cerrar la credencial vigente anterior (si no es esta misma)
            static::where('user_id', $credencial->user_id)
                ->where('id', '!=', $credencial->id)
                ->whereNull('fecha_hasta')
                ->update([
                    'fecha_hasta' => $credencial->fecha_desde->copy()->subDay(),
                ]);

            // Sincronizar caché en users
            $credencial->user()->update([
                'credencial_departamento_id' => $credencial->departamento_id,
                'credencial_serie' => $credencial->serie,
                'credencial_numero' => $credencial->numero,
            ]);
        });
    }
}