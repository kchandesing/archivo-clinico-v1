<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallRequest; // ← Asegúrate de que esta línea exista
use App\Services\InstallationService;
use Exception;
use PDOException;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    protected InstallationService $installationService;

    // Inyección de dependencias SOLID a través del constructor
    public function __construct(InstallationService $installationService)
    {
        $this->installationService = $installationService;
    }

        public function index()
    {
        // Generar formato XXXX-XXXX-XXXX usando Str de Laravel
        $provisionalKey = sprintf(
            '%s-%s-%s',
            \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4)),
            \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4)),
            \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4))
        );

        return view('install.form', compact('provisionalKey'));
    }


    public function store(InstallRequest $request)
    {
        try {
            // Pasamos los datos directo al servicio; el servicio se encargará de guardar la llave en el .env
            $masterKey = $this->installationService->runFullInstallation($request->validated());

            // Redirigir a la pantalla de éxito pasando la llave elegida
            return redirect()->route('install.success')->with('master_key', $masterKey);

        } catch (PDOException $e) {
            Log::error("Syslog_Instalador: Fallo crítico de conexión o sintaxis en PostgreSQL: " . $e->getMessage());
            return back()->withErrors(['error' => 'Fallo de conexión en PostgreSQL: ' . $e->getMessage()])->withInput();
        } catch (Exception $e) {
            Log::error("Syslog_Instalador: Error general en la instalación: " . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
