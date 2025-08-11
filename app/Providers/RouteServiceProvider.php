<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Aquí defines a dónde va el usuario después del login "web".
     */
    public const HOME = '/home';

    /**
     * Bootstraps any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Rutas de API (prefijo /api, middleware api)
            Route::prefix('api')
                 ->middleware('api')
                 // si usas namespaces para controllers, asigna $this->namespace o elimínalo
                 ->group(base_path('routes/api.php'));

            // Rutas web (sin prefijo, middleware web)
            Route::middleware('web')
                 ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configura el rate limiting de tu API.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                        ->by($request->user()?->id ?: $request->ip());
        });
    }
}
