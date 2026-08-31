<?php

namespace App\Services;

use Exception;
use PDO;
use Illuminate\Support\Facades\Artisan;

class InstallationService
{
    protected string $masterKey = 'TU_MASTER_KEY_SUPER_SECRETA_2026'; // Define tu llave aquí

    public function validateMasterKey(string $key): bool
    {
        return $key === $this->masterKey;
    }

    public function createDatabaseAndConfigure(array $data): void
    {
        $dbName = $data['database_name'];
        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', '5433');
        $username = env('DB_USERNAME', 'postgres');
        $password = env('DB_PASSWORD', '');

        // 1. Conectarse a la base de datos por defecto 'postgres' para crear la nueva BD
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=postgres", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificar si la base de datos ya existe
        $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
        $stmt->execute([$dbName]);
        
        if (!$stmt->fetch()) {
            // Crear la base de datos de forma segura (Postgres requiere ejecutarlo directo)
            $pdo->exec("CREATE DATABASE \"$dbName\"");
        }

        // 2. Modificar dinámicamente el archivo .env con el nuevo nombre de la BD
        $this->updateEnvFile('DB_DATABASE', $dbName);
    }

    public function runMigrationsAndSeedAdmin(array $data): void
    {
        // Forzar a Laravel a limpiar la caché de configuración y reconectar a la nueva BD
        Artisan::call('config:purge');
        
        // Ejecutar las migraciones que creamos en el Paso 2
        Artisan::call('migrate', ['--force' => true]);

        // Crear el rol Administrador de forma nativa mediante Eloquent
        $role = \App\Models\Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Acceso total al sistema de archivo clínico']
        );

        // Crear el usuario administrador con los campos solicitados
        \App\Models\User::create([
            'role_id' => $role->id,
            'name' => "{$data['nombres']} {$data['apellido_paterno']}", // Nombre completo para compatibilidad nativa
            'username' => strtolower($data['apellido_paterno'] . substr($data['nombres'], 0, 1)),
            'email' => $data['correo'],
            'password' => bcrypt($data['contraseña']),
            // Si necesitas separar paterno/materno en columnas, se añadirían a la migración de users
        ]);

        // 3. Crear el archivo de bloqueo para evitar que el instalador vuelva a correr
        file_put_contents(storage_path('installed.lock'), 'Installed on ' . date('Y-m-d H:i:s'));
    }

    protected function updateEnvFile(string $key, string $value): void
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            file_put_contents($path, preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                file_get_contents($path)
            ));
        }
    }
}
