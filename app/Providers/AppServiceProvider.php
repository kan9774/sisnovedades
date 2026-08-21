<?php

namespace App\Providers;

use App\Models\Apoyo;
use App\Models\Categoria;
use App\Models\CategoriaDocumento;
use App\Models\Conductor;
use App\Models\Documento;
use App\Models\EstadoPaloma;
use App\Models\Entrega;
use App\Models\Grado;
use App\Models\Guard;
use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\MantenimientoVehiculo;
use App\Models\Movimiento;
use App\Models\News;
use App\Models\Oficina;
use App\Models\Organismo;
use App\Models\Paloma;
use App\Models\Palomar;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\SalidaVehiculo;
use App\Models\Talla;
use App\Models\TipoApoyo;
use App\Models\TipoVehiculo;
use App\Models\Unidad;
use App\Models\UnidadModulo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Vuelo;
use App\Observers\NewsObserver;
use App\Models\Ubicacion;
use App\Observers\UserObserver;
use App\Policies\ApoyoPolicy;
use App\Policies\CategoriaPolicy;
use App\Policies\CategoriaDocumentoPolicy;
use App\Policies\ConductorPolicy;
use App\Policies\DocumentoPolicy;
use App\Policies\EstadoPalomaPolicy;
use App\Policies\EntregaPolicy;
use App\Policies\GradoPolicy;
use App\Policies\GuardiaPdfDestinatarioPolicy;
use App\Policies\GuardiaPolicy;
use App\Policies\ItemPolicy;
use App\Policies\ItemUnidadPolicy;
use App\Policies\MantenimientoVehiculoPolicy;
use App\Policies\MovimientoPolicy;
use App\Policies\NovedadPolicy;
use App\Policies\OficinaPolicy;
use App\Policies\OrganismoPolicy;
use App\Policies\PalomaPolicy;
use App\Policies\PalomarPolicy;
use App\Policies\ProveedorPolicy;
use App\Policies\RolPolicy;
use App\Policies\SalidaVehiculoPolicy;
use App\Policies\TallaPolicy;
use App\Policies\TipoApoyoPolicy;
use App\Policies\TipoVehiculoPolicy;
use App\Policies\UbicacionPolicy;
use App\Policies\UnidadModuloPolicy;
use App\Policies\UnidadPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehiculoPolicy;
use App\Policies\VueloPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Hosts de confianza que NO deben forzar la URL raíz configurada en APP_URL.
     * Cualquier acceso desde estos hosts respeta el host real de la petición,
     * en vez de redirigir siempre a config('app.url').
     */
    protected const HOSTS_LOCALES = [
        'novedades.test',
        'localhost',
        '127.0.0.1',
        'sisnovedades',
        '172.30.105.126',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // path.public ya viene configurado por Laravel en public/
        // No sobreescribir para no romper assets y storage:link
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = request()->getHost();

        if (!in_array($host, self::HOSTS_LOCALES)) {
            URL::forceRootUrl(config('app.url'));
            // No forzamos https porque No-IP anda por http sin certificado SSL
        }

        // Use Bootstrap for pagination views
        Paginator::useBootstrap();

        $this->configureDefaults();

        // Eximir al SuperAdmin de todos los chequeos de autorización
        Gate::before(function (\App\Models\User $user) {
            return $user->isSuperAdmin() ? true : null;
        });

        //Gates para el sidebar de AdminLTE
        Gate::define('viewAny-user', fn($user) => $user->isAdmin()|| $user->HasPermisos('ver_usuario'));
        Gate::define('viewAny-rol', fn($user) => $user->isAdmin());
        Gate::define('viewAny-vehiculo', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_vehiculo'));
        Gate::define('viewAny-conductor', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_conductor'));
        Gate::define('viewAny-vuelo', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_vuelo'));
        Gate::define('viewAny-documento', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_documento'));
        Gate::define('viewAny-tipos-vehiculo', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_tipos_vehiculo'));
        Gate::define('viewAny-tipos-apoyo', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_tipos_apoyo'));
        Gate::define('viewAny-unidades-modulo', fn($user) => $user->isAdmin() || $user->HasPermisos('gestionar_unidades_modulo'));
        Gate::define('view_guardias', fn($user) => $user->isAdmin() || $user->isSuperAdmin() || $user->HasPermisos('ver_guardia'));
        Gate::define('ver_destinatarios_pdf', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_destinatarios_pdf'));
        Gate::define('viewAny-log', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_logs'));
        Gate::define('viewAny-oficina', [OficinaPolicy::class, 'viewAny']);
        Gate::define('viewAny-palomar', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_palomar'));
        Gate::define('viewAny-grado', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_grado'));
        // Gates para el módulo de inventario
        Gate::define('viewAny-item', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_item'));
        Gate::define('viewAny-movimiento', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_item'));
        Gate::define('viewAny-unidad', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_item'));
        Gate::define('viewAny-entrega', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_item'));
        Gate::define('viewAny-categoria', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_categoria'));
        Gate::define('viewAny-talla', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_talla'));
        Gate::define('viewAny-ubicacion', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_ubicacion'));
        Gate::define('viewAny-proveedor', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_proveedores'));
        Gate::define('viewAny-lote', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_item'));
        Gate::define('viwAny-organismo', fn($user)=> $user->isAdmin() || $user->HasPermisos('ver_organismos'));       
        // Gates de los adjuntos de la Guardia
        Gate::define('upload-attach', function (User $user, News $news) {
            if ($user->isAdmin()) {
                return true;
            }

            $tieneRolHabilitado = $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente();

            return $tieneRolHabilitado && $news->guardia->esMiembro($user);
        });

        Gate::define('delete-attach', fn(User $user) => $user->isAdmin());

        // Gates para gestión de backups (solo admins)
        Gate::define('viewAny-backup', fn(User $user) => $user->isAdmin());
        Gate::define('create-backup', fn(User $user) => $user->isAdmin());
        Gate::define('delete-backup', fn(User $user) => $user->isAdmin());
        Gate::define('restore-backup', fn(User $user) => $user->isAdmin());


        // Registrar políticas 
        Gate::policy(Guard::class, GuardiaPolicy::class);
        Gate::policy(News::class, NovedadPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Rol::class, RolPolicy::class);
        Gate::policy(Vehiculo::class, VehiculoPolicy::class);
        Gate::policy(Conductor::class, ConductorPolicy::class);
        Gate::policy(SalidaVehiculo::class, SalidaVehiculoPolicy::class);
        Gate::policy(Palomar::class, PalomarPolicy::class);
        Gate::policy(Paloma::class, PalomaPolicy::class);
        Gate::policy(Vuelo::class, VueloPolicy::class);
        Gate::policy(EstadoPaloma::class, EstadoPalomaPolicy::class);
        Gate::policy(Documento::class, DocumentoPolicy::class);
        Gate::policy(CategoriaDocumento::class, CategoriaDocumentoPolicy::class);
        Gate::policy(MantenimientoVehiculo::class, MantenimientoVehiculoPolicy::class);
        Gate::policy(TipoVehiculo::class, TipoVehiculoPolicy::class);
        Gate::policy(TipoApoyo::class, TipoApoyoPolicy::class);
        Gate::policy(Apoyo::class, ApoyoPolicy::class);
        Gate::policy(Unidad::class, UnidadPolicy::class);
        Gate::policy(UnidadModulo::class, UnidadModuloPolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(ItemUnidad::class, ItemUnidadPolicy::class);
        Gate::policy(Movimiento::class, MovimientoPolicy::class);
        Gate::policy(Entrega::class, EntregaPolicy::class);
        Gate::policy(Categoria::class, CategoriaPolicy::class);
        Gate::policy(Ubicacion::class, UbicacionPolicy::class);
        Gate::policy(Proveedor::class, ProveedorPolicy::class);
        Gate::policy(Talla::class, TallaPolicy::class);
        Gate::policy(Grado::class, GradoPolicy::class);
        Gate::policy(Oficina::class, OficinaPolicy::class);
        Gate::policy(Organismo::class, OrganismoPolicy::class);


        // Observers
        News::observe(NewsObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}
