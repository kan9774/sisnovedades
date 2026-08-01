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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
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
#[Fillable(['name', 'last_name', 'grado_id', 'email', 'password', 'unidad_id', 'oficina_id', 'status', 'is_super_admin', 'must_change_password', 'segundo_nombre', 'segundo_apellido', 'fecha_nacimiento', 'ci'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, SoftDeletes, LogsActivity;
    use MustVerifyEmailTrait {
        MustVerifyEmailTrait::sendEmailVerificationNotification as protected traitSendEmailVerificationNotification;
    }
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'status' => \App\Enums\UserStatus::class,
        ];
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

    /**
     * El Super Admin usa un email ficticio (no puede recibir el correo de
     * verificación), así que queda exento sin importar email_verified_at.
     * El resto de los usuarios sigue el comportamiento normal del trait.
     */
    public function hasVerifiedEmail(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Usuarios');
    }

    // Relación con la unidad a la que pertenece el usuario
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }

    public function direcciones(): HasMany
    {
        return $this->hasMany(Direccion::class);
    }

    public function direccionPrincipal(): HasOne
    {
        return $this->hasOne(Direccion::class)->where('es_principal', true);
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

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

    /**
     * Roles asignados al usuario. Un usuario puede tener más de uno.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'role_user', 'user_id', 'rol_id');
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class);
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * Historial completo de cambios de grado (ascensos y degradaciones),
     * del más reciente al más antiguo.
     */
    public function historialGrados(): HasMany
    {
        return $this->hasMany(HistorialGrado::class)->latest('fecha_cambio');
    }

    /**
     * Último cambio de grado registrado (el que determina el grado
     * vigente). Devuelve null si todavía no se cargó ningún historial
     * para este usuario.
     */
    public function ultimoCambioGrado(): ?HistorialGrado
    {
        return $this->historialGrados()->first();
    }

    /**
     * Compatibilidad: todo el código que hace {{ $user->grade }} sigue
     * funcionando sin tocarlo, porque Eloquent resuelve este accessor
     * como si fuera la columna original.
     */
    public function getGradeAttribute(): ?string
    {
        return $this->grado?->nombre;
    }
    /**
     * Permisos asignados directamente al usuario, además de los de sus roles.
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
     * Unidades individuales de inventario (PC, radio, silla, etc.)
     * actualmente asignadas a este usuario como responsable.
     */
    public function itemUnidadesAsignadas(): HasMany
    {
        return $this->hasMany(ItemUnidad::class, 'responsable_id');
    }

    /**
     * Verificar si el usuario es Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Verificar si el usuario tiene un rol determinado por nombre.
     * Comparación case-insensitive: los roles se crean desde el form de
     * Roles y el casing con que se guardan (p. ej. "Escribiente",
     * "Capitan_de_Servicio") no siempre coincide con el string en minúscula
     * que usan los métodos isX() de este modelo.
     */
    public function tieneRol(string $nombre): bool
    {
        return $this->roles->contains(
            fn($rol) => strcasecmp($rol->name, $nombre) === 0
        );
    }

    /**
     * Verificar si el usuario es Admin (incluye Super Admin)
     */
    public function isAdmin(): bool
    {
        return $this->tieneRol('admin') || $this->isSuperAdmin();
    }

    /**
     * Verificar si el usuario es Oficial de Día
     */
    public function isOficialDia(): bool
    {
        return $this->tieneRol('oficial_de_dia');
    }

    /**
     * Verificar si el usuario es Capitán
     */
    public function isCapitan(): bool
    {
        return $this->tieneRol('capitan_de_servicio');
    }

    /**
     * Verificar si el usuario es Escribiente
     */
    public function isEscribiente(): bool
    {
        return $this->tieneRol('escribiente');
    }

    /**
     * Verificar si el usuario tiene un permiso específico,
     * heredado de cualquiera de sus roles o asignado directamente.
     */
    public function HasPermisos(string $permiso): bool
    {
        if ($this->permisosDirectos->contains('name', $permiso)) {
            return true;
        }

        foreach ($this->roles as $rol) {
            if ($rol->permisos->contains('name', $permiso)) {
                return true;
            }
        }

        return false;
    }
    /**
     * Accessor para obtener el primer rol (compatibilidad con código existente)
     */
    public function getRolAttribute()
    {
        return $this->roles()->first();
    }

    /**
     * Accessor para obtener el nombre del rol principal
     */
    public function getRolNameAttribute()
    {
        $roleName = $this->roles()->first()->name ?? 'Sin rol';
        return str_replace('_', ' ', $roleName);
    }

    /**
     * Accessor para obtener todos los roles como string
     */
    public function getRolesListAttribute()
    {
        $roles = $this->roles->pluck('name')->map(function ($name) {
            return str_replace('_', ' ', $name);
        })->implode(', ') ?: 'Sin rol';

        return $roles;
    }
    protected static function calcularDigitoVerificadorCi(string $ci): int
    {
        $ci = str_pad($ci, 7, '0', STR_PAD_LEFT);
        $coeficientes = [2, 9, 8, 7, 6, 3, 4];
        $suma = 0;

        foreach (str_split($ci) as $i => $digito) {
            $suma += (int) $digito * $coeficientes[$i];
        }

        $resto = $suma % 10;

        return $resto === 0 ? 0 : 10 - $resto;
    }

    public function setCiAttribute(?string $value): void
    {
        if (blank($value)) {
            $this->attributes['ci'] = null;
            $this->attributes['ci_dv'] = null;
            return;
        }

        // Limpia puntos, guiones, espacios - solo deja dígitos
        $ci = preg_replace('/\D/', '', $value);

        // Si vino con 8 dígitos (incluye el verificador ya cargado), lo recorta a 7
        if (strlen($ci) === 8) {
            $ci = substr($ci, 0, 7);
        }

        $ci = str_pad($ci, 7, '0', STR_PAD_LEFT);

        $this->attributes['ci'] = $ci;
        $this->attributes['ci_dv'] = self::calcularDigitoVerificadorCi($ci);
    }

    public function getCiCompletoAttribute(): ?string
    {
        return $this->ci ? "{$this->ci}-{$this->ci_dv}" : null;
    }

    public function getCiFormateadoAttribute(): ?string
    {
        if (!$this->ci) {
            return null;
        }

        // Formato tipo 1.234.567-8
        $conPuntos = number_format((int) $this->ci, 0, '', '.');

        return "{$conPuntos}-{$this->ci_dv}";
    }
    /**
     * Historial completo de altas/bajas del Ejército, del más reciente
     * al más antiguo. Distinto de `users.status` (activo en la app):
     * esto es si está activo en el Ejército o no.
     */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstado::class)->latest('fecha');
    }

    /**
     * Último movimiento de alta/baja registrado. Null si todavía no se
     * cargó ningún historial para este usuario.
     */
    public function ultimoEstado(): ?HistorialEstado
    {
        return $this->historialEstados()->first();
    }

    /**
     * true si el último movimiento fue un alta (está activo en el
     * Ejército). Un usuario sin historial se considera sin definir,
     * no activo.
     */
    public function estaActivoEnElEjercito(): bool
    {
        return $this->ultimoEstado()?->tipo === 'alta';
    }

    /**
     * Cuántas altas le quedan disponibles antes de agotar el máximo de
     * ingreso + reingresos (HistorialEstado::MAX_ALTAS).
     */
    public function altasRestantes(): int
    {
        $altasUsadas = $this->historialEstados()->where('tipo', 'alta')->count();

        return max(0, HistorialEstado::MAX_ALTAS - $altasUsadas);
    }
}
