<?php

namespace App\Repositories\Eloquent; // <- Revisa las mayúsculas

use App\Models\Paciente;
use App\Repositories\Contracts\PacienteRepositoryInterface; // <- Esta importación es obligatoria
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PacienteRepository implements PacienteRepositoryInterface
{
    protected Paciente $model;

    public function __construct(Paciente $model)
    {
        $this->model = $model;
    }

    /**
     * Implementación del Buscador Global Predictivo usando pg_trgm.
     */
    public function searchGlobal(string $term): Collection
    {
        if (empty($term)) {
            return $this->model->with(['direccion', 'ubicacionFisica'])->limit(10)->get();
        }

        // Búsqueda en paralelo: número de expediente exacto, CURP o similitud de nombres combinados
        return $this->model->with(['direccion', 'ubicacionFisica'])
            ->where('numero_expediente', 'ILIKE', "%{$term}%")
            ->orWhere('curp', 'ILIKE', "%{$term}%")
            ->orWhere(DB::raw("CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno)"), 'ILIKE', "%{$term}%")
            ->limit(20)
            ->get();
    }

    /**
     * Transacción atómica para garantizar que si falla el registro de la dirección o ubicación,
     * no se guarde un paciente incompleto (Principio de Integridad).
     */
    public function storeWithDetails(array $pacienteData, array $direccionData, array $ubicacionData): Paciente
    {
        return DB::transaction(function () use ($pacienteData, $direccionData, $ubicacionData) {
            
            // 1. Guardar datos demográficos del Paciente
            $paciente = $this->model->create($pacienteData);

            // 2. Guardar Dirección vinculada
            $direccionData['id_paciente'] = $paciente->id_paciente;
            $paciente->direccion()->create($direccionData);

            // 3. Guardar Ubicación Física en Archivo
            $ubicacionData['id_paciente'] = $paciente->id_paciente;
            $ubicacionData['estado_expediente'] = $ubicacionData['estado_expediente'] ?? 'En Archivo';
            $paciente->ubicacionFisica()->create($ubicacionData);

            return $paciente;
        });
    }

    public function findWithDetails(int $id): ?Paciente
    {
        return $this->model->with(['direccion', 'ubicacionFisica', 'usuarioRegistro'])->find($id);
    }
}
