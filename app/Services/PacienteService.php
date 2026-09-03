<?php

namespace App\Services;

use App\Models\Paciente;
use App\Repositories\Contracts\PacienteRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PacienteService
{
    protected PacienteRepositoryInterface $pacienteRepository;

    /**
     * Inyección de dependencias del Repositorio (SOLID).
     */
    public function __construct(PacienteRepositoryInterface $pacienteRepository)
    {
        $this->pacienteRepository = $pacienteRepository;
    }

    /**
     * Coordina la búsqueda global predictiva pasándole los datos sanitizados al repositorio.
     */
    public function searchPacientes(string $term): Collection
    {
        $sanitizedTerm = trim($term);
        return $this->pacienteRepository->searchGlobal($sanitizedTerm);
    }

    /**
     * Prepara, estructura y limpia los tres bloques de datos antes del guardado atómico.
     */
    public function createNewPaciente(array $validatedData): Paciente
    {
        Log::info("Syslog_Clinico: Iniciando procesamiento de registro para un nuevo expediente.");

        // 1. Aislar y formatear los datos demográficos del Paciente
        $esProvisional = isset($validatedData['es_provisional']) && $validatedData['es_provisional'] == 1;
        // 1. Aislar y formatear los datos demográficos del Paciente
        $pacienteData = [
            'es_provisional'      => $esProvisional,
            // Si es provisional el CURP se anula; si no, se procesa en mayúsculas
            'curp'                => $esProvisional ? null : Str::upper($validatedData['curp']),
            'nombres'             => Str::title(trim($validatedData['nombres'])),
            'apellido_paterno'    => Str::title(trim($validatedData['apellido_paterno'])),
            'apellido_materno'    => isset($validatedData['apellido_materno']) ? Str::title(trim($validatedData['apellido_materno'])) : null,
            'fecha_nacimiento'    => $validatedData['fecha_nacimiento'],
            'sexo'                => $validatedData['sexo'],
            'id_usuario_registro' => Auth::id(),
        ];

        // 2. Aislar y estructurar los datos domiciliarios
        $direccionData = [
            'calle'           => Str::title(trim($validatedData['calle'])),
            'numero_exterior' => trim($validatedData['numero_exterior']),
            'numero_interior' => isset($validatedData['numero_interior']) ? trim($validatedData['numero_interior']) : null,
            'colonia'         => Str::title(trim($validatedData['colonia'])),
            'codigo_postal'   => $validatedData['codigo_postal'],
            'localidad'       => Str::title(trim($validatedData['localidad'])),
            'municipio'       => Str::title(trim($validatedData['municipio'])),
            'estado'          => Str::title(trim($validatedData['estado'])),
        ];

        // 3. Aislar y estructurar la localización física provisional del expediente en el archivo
        $ubicacionData = [
            'pasillo'           => isset($validatedData['pasillo']) ? Str::upper(trim($validatedData['pasillo'])) : null,
            'estante'           => isset($validatedData['estante']) ? Str::upper(trim($validatedData['estante'])) : null,
            'caja'              => isset($validatedData['caja']) ? Str::upper(trim($validatedData['caja'])) : null,
            'estado_expediente' => 'En Archivo' // Estado inicial nativo
        ];

        try {
            // Mandar los bloques limpios al repositorio bajo una transacción SQL
            $paciente = $this->pacienteRepository->storeWithDetails($pacienteData, $direccionData, $ubicacionData);

            Log::info("Syslog_Clinico: Expediente registrado exitosamente. ID Paciente: {$paciente->id_paciente}. Operador ID: " . Auth::id());

            return $paciente;
        } catch (\Exception $e) {
            Log::error("Syslog_Clinico: Error catastrófico al persistir el expediente en PostgreSQL: " . $e->getMessage());
            throw new \Exception("No se pudo completar el registro médico debido a un problema con el motor de datos.");
        }
    }

    /**
     * Trae un expediente completo con su historial clínico y localización.
     */
    public function getPacienteDetails(int $id): ?Paciente
    {
        return $this->pacienteRepository->findWithDetails($id);
    }
}
