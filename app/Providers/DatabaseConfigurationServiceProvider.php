<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

class DatabaseConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios de la aplicación.
     */
    public function register(): void
    {
        // 1. Verificar si el sistema ya fue instalado leyendo el .env
        $encryptedData = env('DB_ENCRYPTED_DATA');

        if (!empty($encryptedData)) {
            try {
                // 2. Desencriptar las credenciales usando la APP_KEY maestra del software
                $decrypted = Crypt::decrypt($encryptedData);

                if (is_array($decrypted)) {
                    // 3. Inyectar dinámicamente los valores reales en la configuración de Laravel
                    Config::set('database.connections.pgsql.host', $decrypted['host']);
                    Config::set('database.connections.pgsql.port', $decrypted['port']);
                    Config::set('database.connections.pgsql.database', $decrypted['database']);
                    Config::set('database.connections.pgsql.username', $decrypted['username']);
                    Config::set('database.connections.pgsql.password', $decrypted['password']);
                }
            } catch (Exception $e) {
                // Si la APP_KEY cambió o la cadena está corrupta, lo reportamos en el Syslog
                Log::error("Syslog_Seguridad: No se pudieron descifrar las credenciales de la base de datos. Cadena corrupta o APP_KEY alterada.");
            }
        }
    }

    /**
     * Arranca los servicios de la aplicación.
     */
    public function boot(): void
    {
        //
    }
}
