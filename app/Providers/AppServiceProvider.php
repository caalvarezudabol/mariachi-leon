<?php

namespace App\Providers;

use App\Http\Middleware\CheckSessionTimeout;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El control de inactividad también debe ejecutarse en las peticiones internas de Livewire,
        // para que usar la pantalla (clics, búsquedas, guardados) cuente como actividad.
        Livewire::addPersistentMiddleware([
            CheckSessionTimeout::class,
        ]);
    }
}