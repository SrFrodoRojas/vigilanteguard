<?php
namespace App\Providers;

use App\Models\PatrolAssignment;
use App\Policies\PatrolAssignmentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Nada por ahora
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Superadmin: todos los permisos
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // TZ y fechas en español
        date_default_timezone_set(Config::get('app.timezone', 'America/Asuncion'));
        Date::use (Carbon::class);
        Carbon::setLocale(Config::get('app.locale', 'es'));

        // Paginador con Bootstrap (AdminLTE 3 usa BS4)
        if (method_exists(Paginator::class, 'useBootstrapFour')) {
            Paginator::useBootstrapFour();
        } elseif (method_exists(Paginator::class, 'useBootstrap')) {
            // Compatibilidad con versiones donde useBootstrapFour no existe
            Paginator::useBootstrap();
        }
        Gate::policy(PatrolAssignment::class, PatrolAssignmentPolicy::class);
        RateLimiter::for('patrol-scan', function ($request) {
            return [
                Limit::perMinute(12)->by(optional($request->user())->id ?: $request->ip()),
                Limit::perSecond(1)->by(optional($request->user())->id ?: $request->ip()),
            ];
        });

    }
}
