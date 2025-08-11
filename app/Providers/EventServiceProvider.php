<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Si quieres que Laravel descubra automáticamente tus eventos,
     * debe ser una propiedad estática, sin declarar el tipo.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * Mapas de eventos → listeners.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // \App\Events\SomeEvent::class => [
        //     \App\Listeners\SomeListener::class,
        // ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
