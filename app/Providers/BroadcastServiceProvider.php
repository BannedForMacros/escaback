<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Registra los canales de broadcast de la aplicación.
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        /*
         * Aquí puedes registrar canales privados y públicos.
         * Ejemplo:
         *
         * Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
         *     return $user->id === Order::findOrNew($orderId)->user_id;
         * });
         */
    }
}
