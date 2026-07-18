<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuardiaPdfDestinatario extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'detalles',
        'color',
    ];

    protected $casts = [
        'detalles' => 'string',
    ];

    /**
     * Usuarios asignados a este destinatario
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardia_pdf_destinatarios_users', 'destinatario_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Obtener usuarios con emails válidos
     */
    public function usuariosConEmail(): BelongsToMany
    {
        return $this->usuarios()
                    ->with('oficina')
                    ->whereNotNull('users.email')
                    ->orderBy('users.name');
    }
}
