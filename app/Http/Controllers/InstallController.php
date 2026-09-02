<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallRequest;
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
        return view('install.form');
    }

    public function store(InstallRequest $request)
    {
        // 1. Validar la Master Key antes de tocar cualquier base de datos
        if (!$this->installationService->validateMasterKey($request->master_key)) {
            return back()->withErrors(['master_key' => 'La Master Key proporcionada es incorrecta.'])->withInput();
        }

        try {
            // 2. Ejecutar el flujo de aprovisionamiento usando el esquema SQL montado
            $this->installationService->runFullInstallation($request->validated());

            return redirect()->route('login')->with('success', '¡Sistema inicializado correctamente!');
        } catch (PDOException $e) {
            Log::error("Syslog_Instalador: Fallo crítico de conexión o sintaxis en PostgreSQL: " . $e->getMessage());
            return back()->withErrors(['error' => 'Fallo de conexión o sintaxis en PostgreSQL: ' . $e->getMessage()])->withInput();
        } catch (Exception $e) {
            Log::error("Syslog_Instalador: Error general en el proceso de instalación: " . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
