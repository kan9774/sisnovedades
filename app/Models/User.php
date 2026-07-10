<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'last_name', 'grade', 'email', 'password', 'rol_id', 'unidad_id', 'oficina_id', 'status', 'is_super_admin'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, softDeletes, LogsActivity;
    use MustVerifyEmailTrait {
        MustVerifyEmailTrait::sendEmailVerificationNotification as protected traitSendEmailVerificationNotification;
    }

    /**
     * Envía el mail de verificación SOLO si la feature está activada en config.
     * Mientras el sistema esté en pruebas (EMAIL_VERIFICATION_ENABLED=false en .env),
     * este método no hace nada y ningún usuario recibe el correo.
     */
    public function sendEmailVerificationNotification(): void
    {
        if (config('fortify.email_verification_enabled', false)) {
            $this->traitSendEmailVerificationNotification();
        }
    }



    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Usuarios'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    // Relación con la unidad a la que pertenece el usuario
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }
    

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1) . Str::substr($initials, -1)
            : $initials;
    }
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class);
    }

    /**
     * Permisos asignados directamente al usuario, además de los de su rol.
     */
    public function permisosDirectos(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guard::class, 'oficer_id');
    }
    public function guardiasComoCapitan(): HasMany
    {
        return $this->hasMany(Guard::class, 'captain_id');
    }
    public function novedades(): HasMany
    {
        return $this->hasMany(News::class, 'escribiente_id');
    }

    /**
     * Verificar si el usuario es Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Verificar si el usuario es Admin (incluye Super Admin)
     */
    public function isAdmin(): bool
    {
        return $this->rol?->name === 'admin' || $this->isSuperAdmin();
    }

    /**
     * Verificar si el usuario es Oficial de Día
     */
    public function isOficialDia(): bool
    {
        return $this->rol?->name === 'oficial_de_dia';
    }

    /**
     * Verificar si el usuario es Capitán
     */
    public function isCapitan(): bool
    {
        return $this->rol?->name === 'capitan_de_servicio';
    }

    /**
     * Verificar si el usuario es Escribiente
     */
    public function isEscribiente(): bool
    {
        return $this->rol?->name === 'escribiente';
    }

    /**
     * Verificar si el usuario tiene un permiso específico,
     * ya sea heredado de su rol o asignado directamente.
     */
    public function HasPermisos(string $permiso): bool
    {
        return $this->rol?->permisos->contains('name', $permiso)
            || $this->permisosDirectos->contains('name', $permiso);
    }
}