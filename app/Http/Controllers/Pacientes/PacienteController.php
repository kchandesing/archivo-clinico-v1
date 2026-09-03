<?php

namespace App\Http\Controllers\Pacientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pacientes\StorePacienteRequest;
use App\Services\PacienteService;
use Illuminate\Http\Request;
use Exception;

class PacienteController extends Controller
{
    protected PacienteService $pacienteService;

    /**
     * Inyección de dependencias del Servicio de Negocio (SRP / SOLID).
     */
    public function __construct(PacienteService $pacienteService)
    {
        $this->pacienteService = $pacienteService;
    }

    /**
     * Muestra la pantalla principal de pacientes (Buscador y Tabla).
     */
    public function index(Request $request)
    {
        // Capturar el término de búsqueda si el usuario escribió algo
        $searchTerm = $request->input('query', '');
        
        // Consumir el buscador global del servicio
        $pacientes = $this->pacienteService->searchPacientes($searchTerm);

        // Retornar la vista index pasándole los resultados y el término buscado
        return view('pacientes.index', compact('pacientes', 'searchTerm'));
    }

    /**
     * Muestra el formulario para registrar un nuevo expediente.
     */
    public function create()
    {
        return view('pacientes.create');
    }

    /**
     * Procesa y almacena de forma segura los datos enviados desde el formulario.
     */
    public function store(StorePacienteRequest $request)
    {
        try {
            // Mandar los datos validados del Form Request directamente al servicio
            $this->pacienteService->createNewPaciente($request->validated());

            // Redirigir a la tabla principal con un mensaje de éxito corporativo
            return redirect()->route('pacientes.index')->with('success', 'El expediente médico ha sido creado y archivado exitosamente.');
        } catch (Exception $e) {
            // Si algo falla en PostgreSQL, regresa al formulario con el error sin romper la app
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Muestra la ventana de detalle o ficha completa de un expediente específico.
     */
    public function show(int $id)
    {
        $paciente = $this->pacienteService->getPacienteDetails($id);

        if (!$paciente) {
            return redirect()->route('pacientes.index')->withErrors(['error' => 'El expediente solicitado no existe o fue removido.']);
        }

        return view('pacientes.show', compact('paciente'));
    }
}
