<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registra los enlaces del contenedor de servicios.
     */
    public function register(): void
    {
        // Enlace formal de la interfaz a la implementación concreta
        $this->app->bind(PacienteRepositoryInterface::class, PacienteRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
