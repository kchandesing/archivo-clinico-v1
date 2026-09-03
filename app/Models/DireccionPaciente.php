<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionPaciente extends Model
{
    protected $table = 'direcciones_pacientes';
    
    protected $primaryKey = 'id_direccion';

    // Desactivar timestamps automáticos ya que esta tabla intermedia no los requiere en tu script
    public $timestamps = false;

    protected $fillable = [
        'id_paciente',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'codigo_postal',
        'localidad',
        'municipio',
        'estado'
    ];

    /**
     * Relación Inversa: La Dirección pertenece estrictamente a un Paciente.
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }
}
