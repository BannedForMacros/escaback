<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     * Estos se ejecutan en cada petición.
     *
     * @var array<int, class-string>
     */
    protected $middleware = [
        // Confía en proxies (si usas load balancers, etc.)
        \App\Http\Middleware\TrustProxies::class,
        // Manejo de CORS
        \Fruitcake\Cors\HandleCors::class,
        // Mantenimiento
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        // Valida tamaño máximo de POST
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // Limpia espacios en cadenas
        \App\Http\Middleware\TrimStrings::class,
        // Convierte cadenas vacías a null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // Cifra cookies
            \App\Http\Middleware\EncryptCookies::class,
            // Añade cookies encoladas
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // Inicia la sesión
            \Illuminate\Session\Middleware\StartSession::class,
            // Comparte errores de validación en la vista
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // Verificación CSRF
            \App\Http\Middleware\VerifyCsrfToken::class,
            // Resuelve bindings en rutas
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // Para que Sanctum gestione correctamente solicitudes stateful desde el front
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // Límite de peticiones por minuto (configurado en config/api.php o en tu throttle)
            'throttle:api',
            // Resuelve bindings en rutas
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Route middleware.
     * Estos puedes asignarlos a rutas individuales.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'           => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
