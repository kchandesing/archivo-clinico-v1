<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paciente extends Model
{
    // 1. Apuntar a la tabla en español de tu script SQL
    protected $table = 'pacientes';

    // 2. Definir la llave primaria explícita
    protected $primaryKey = 'id_paciente';

    // 3. Mapear las columnas de tiempo nativas a tu esquema de Postgres
    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_modificacion';

    // 4. Habilitar la asignación masiva de campos obligatorios y opcionales
    protected $fillable = [
        'numero_expediente',
        'curp',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'es_provisional',
        'id_usuario_registro'
    ];

    // 5. Castear los tipos de datos nativos para su manipulación en PHP
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'es_provisional'   => 'boolean',
        'fecha_registro'   => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    /**
     * Relación: Un Paciente tiene una única Dirección asociada.
     */
    public function direccion(): HasOne
    {
        // Parámetros: Modelo Destino, FK en tabla destino, Local Key en esta tabla
        return $this->hasOne(DireccionPaciente::class, 'id_paciente', 'id_paciente');
    }

    /**
     * Relación: Un Paciente tiene una única Ubicación Física registrada en el archivo.
     */
    public function ubicacionFisica(): HasOne
    {
        return $this->hasOne(LocalizacionFisica::class, 'id_paciente', 'id_paciente');
    }

    /**
     * Relación: Un Paciente fue registrado por un Usuario específico del sistema.
     */
    public function usuarioRegistro(): BelongsTo
    {
        // Parámetros: Modelo Destino, FK en esta tabla, Owner Key en tabla destino
        return $this->belongsTo(User::class, 'id_usuario_registro', 'id_usuario');
    }
}
