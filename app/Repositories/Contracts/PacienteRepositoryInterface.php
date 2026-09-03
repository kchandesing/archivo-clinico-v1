<?php

namespace App\Repositories\Contracts;

use App\Models\Paciente;
use Illuminate\Support\Collection;

interface PacienteRepositoryInterface
{
    /**
     * Busca pacientes de forma predictiva global (Expediente, CURP, o Nombres combinados).
     * Consume el índice trigram (pg_trgm) nativo de PostgreSQL.
     */
    public function searchGlobal(string $term): Collection;

    /**
     * Almacena un paciente junto con su dirección y localización física opcional.
     */
    public function storeWithDetails(array $pacienteData, array $direccionData, array $ubicacionData): Paciente;

    /**
     * Busca un paciente específico por su ID trayendo sus relaciones completas.
     */
    public function findWithDetails(int $id): ?Paciente;
}
