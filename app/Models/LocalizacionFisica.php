<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalizacionFisica extends Model
{
    protected $table = 'localizaciones_fisicas';

    protected $primaryKey = 'id_localizacion';

    public $timestamps = false;

    protected $fillable = [
        'id_paciente',
        'pasillo',
        'estante',
        'caja',
        'estado_expediente' // Valores esperados: 'En Archivo', 'Prestado'
    ];

    /**
     * Relación Inversa: La Ubicación Física pertenece a un Expediente de Paciente.
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente', 'id_paciente');
    }
}
