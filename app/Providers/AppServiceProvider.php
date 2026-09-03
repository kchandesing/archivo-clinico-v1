<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// 1. IMPORTACIONES CRÍTICAS (Asegura las mayúsculas exactas)
use App\Repositories\Contracts\PacienteRepositoryInterface;
use App\Repositories\Eloquent\PacienteRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios de la aplicación.
     */
    public function register(): void
    {
        // 2. ENLACE DIRECTO EN EL PROVEEDOR CORE
        $this->app->bind(PacienteRepositoryInterface::class, PacienteRepository::class);
    }

    /**
     * Arranca los servicios de la aplicación.
     */
    public function boot(): void
    {
        //
    }
}
