<?php

namespace App\Providers;

use App\Models\CategoriaDocumento;
use App\Models\Conductor;
use App\Models\Documento;
use App\Models\EstadoPaloma;
use App\Models\Guard;
use App\Models\News;
use App\Models\Paloma;
use App\Models\Palomar;
use App\Models\Rol;
use App\Models\SalidaVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Vuelo;
use App\Observers\NewsObserver;
use App\Policies\CategoriaDocumentoPolicy;
use App\Policies\ConductorPolicy;
use App\Policies\DocumentoPolicy;
use App\Policies\EstadoPalomaPolicy;
use App\Policies\GuardiaPolicy;
use App\Policies\NovedadPolicy;
use App\Policies\PalomaPolicy;
use App\Policies\PalomarPolicy;
use App\Policies\RolPolicy;
use App\Policies\SalidaVehiculoPolicy;
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
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('path.public', function () {
            return storage_path() . '/public_html';
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = request()->getHost();

        if (!in_array($host, ['novedades.test', 'localhost', '127.0.0.1'])) {
            URL::forceRootUrl(config('app.url'));
            // No forzamos https porque No-IP anda por http sin certificado SSL
        }

        // Use Bootstrap for pagination views
        Paginator::useBootstrap();

        $this->configureDefaults();

        //Gates para el sidebar de AdminLTE
        Gate::define('viewAny-user', fn($user) => $user->isAdmin());
        Gate::define('viewAny-rol', fn($user) => $user->isAdmin());
        Gate::define('viewAny-vehiculo', fn($user) => $user->isAdmin() || $user->isCapitan() || $user->isOficialDia());
        Gate::define('viewAny-conductor', fn($user) => $user->isAdmin() || $user->isCapitan() || $user->isOficialDia());
        Gate::define('viewAny-vuelo', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_vuelo'));
        Gate::define('viewAny-documento', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_documento'));
        Gate::define('viewAny-documento', fn($user) => $user->isAdmin() || $user->HasPermisos('ver_documento'));


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


        // Observers
        News::observe(NewsObserver::class);
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
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}
