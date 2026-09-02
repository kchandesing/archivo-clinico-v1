<?php

namespace App\Services;

use Exception;
use PDO;
use PDOException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class InstallationService
{
    /**
     * Procesa la instalación completa y retorna la Master Key utilizada.
     */

    public function runFullInstallation(array $data): string
    {
        Log::info("Syslog_Instalador: Iniciando aprovisionamiento con Master Key configurada.");

        // Capturar la llave que el usuario envió (sea la sugerida o la personalizada)
        $masterKey = $data['master_key'];

        // Conexión Maestra a 'postgres'
        $pdoInit = $this->createMasterConnection($data);
        $this->checkIfDatabaseExists($pdoInit, $data['database_name']);

        // Crear la base de datos física
        $pdoInit->exec("CREATE DATABASE " . $this->stringToPostgresIdentifier($data['database_name']));
        unset($pdoInit); 

        // Conexión formal a la base de datos clínica recién creada e inyección de esquema
        $pdoClinica = $this->createClinicaConnection($data);
        $this->injectSqlSchema($pdoClinica);

        // Insertar primer Administrador
        $this->seedInitialAdmin($pdoClinica, $data);
        unset($pdoClinica);

        // Encriptar credenciales mediante la APP_KEY maestra
        $encryptedString = $this->encryptDatabaseCredentials($data);

        // Escribir los datos en el archivo .env (Guardando de forma nativa la llave elegida)
        $this->updateEnvironmentFile($encryptedString, $masterKey);

        touch(storage_path('installed.lock'));

        Log::info("Syslog_Instalador: Instalación finalizada con éxito.");

        return $masterKey;
    }

    protected function createMasterConnection(array $data): PDO
    {
        $dsn = "pgsql:host={$data['db_host']};port={$data['db_port']};dbname=postgres";
        return new PDO($dsn, $data['db_username'], $data['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    protected function checkIfDatabaseExists(PDO $pdo, string $dbName): void
    {
        $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
        $stmt->execute([$dbName]);
        
        if ($stmt->fetch()) {
            Log::warning("Syslog_Instalador: Intento fallido de instalación. La base de datos '$dbName' ya existe.");
            throw new Exception("La base de datos especificada ya existe en el servidor PostgreSQL.");
        }
    }

    protected function createClinicaConnection(array $data): PDO
    {
        $dsn = "pgsql:host={$data['db_host']};port={$data['db_port']};dbname={$data['database_name']}";
        return new PDO($dsn, $data['db_username'], $data['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    protected function injectSqlSchema(PDO $pdo): void
    {
        $sqlPath = database_path('sql/database_schema.sql');
        if (!file_exists($sqlPath)) {
            Log::error("Syslog_Instalador: Archivo SQL ausente en la ruta esperada.");
            throw new Exception("No se encontró el archivo de estructura SQL en: database/sql/database_schema.sql");
        }
        
        $sqlSchema = file_get_contents($sqlPath);
        $pdo->exec($sqlSchema); 
        Log::info("Syslog_Instalador: Inyección de esquema SQL, índices trigram y triggers JSONB finalizada.");
    }

    protected function seedInitialAdmin(PDO $pdo, array $data): void
    {
        // Encriptar la contraseña usando el Hash nativo de Laravel
        $passwordHash = Hash::make($data['admin_password']);
        
        // id_rol = 1 mapea estrictamente a 'Administrador' según tu script SQL
        $stmtAdmin = $pdo->prepare("
            INSERT INTO usuarios (id_rol, nombres, apellido_paterno, apellido_materno, email, password, activo) 
            VALUES (1, ?, ?, ?, ?, ?, TRUE)
        ");
        
        $stmtAdmin->execute([
            $data['admin_nombres'],
            $data['admin_paterno'],
            $data['admin_materno'] ?? null,
            $data['admin_email'],
            $passwordHash
        ]);

        Log::info("Syslog_Instalador: Cuenta del primer administrador '" . $data['admin_email'] . "' dada de alta con éxito.");
    }

    protected function encryptDatabaseCredentials(array $data): string
    {
        $credentialsArray = [
            'host'     => $data['db_host'],
            'port'     => $data['db_port'],
            'database' => $data['database_name'],
            'username' => $data['db_username'],
            'password' => $data['db_password']
        ];
        
        return Crypt::encrypt($credentialsArray);
    }

    protected function updateEnvironmentFile(string $encryptedData): void
    {
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            // Cambiamos el estado de instalación y agregamos los datos cifrados
            $envContent = preg_replace('/^APP_INSTALLED=.*/m', 'APP_INSTALLED=true', $envContent);
            if (!str_contains($envContent, 'APP_INSTALLED=')) {
                $envContent .= "\nAPP_INSTALLED=true";
            }

            if (str_contains($envContent, 'DB_ENCRYPTED_DATA=')) {
                $envContent = preg_replace('/^DB_ENCRYPTED_DATA=.*/m', 'DB_ENCRYPTED_DATA="' . $encryptedData . '"', $envContent);
            } else {
                $envContent .= "\nDB_ENCRYPTED_DATA=\"" . $encryptedData . "\"";
            }

            // Forzar que se mantenga 'pgsql' activo en lugar de ponerlo en null
            $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=pgsql', $envContent);

            file_put_contents($envPath, $envContent);
            Log::info("Syslog_Instalador: Archivo .env modificado y guardado físicamente.");
        }
    }

    private function stringToPostgresIdentifier(string $string): string
    {
        return '"' . str_replace('"', '""', $string) . '"';
    }
}
