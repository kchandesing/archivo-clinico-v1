<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // 1. Indicar el nombre exacto de la tabla de tu script SQL
    protected $table = 'roles';

    // 2. Definir la llave primaria personalizada
    protected $primaryKey = 'id_rol';

    // 3. Desactivar los timestamps si tu tabla 'roles' no tiene created_at/updated_at
    public $timestamps = false;

    // 4. Habilitar la asignación masiva de campos
    protected $fillable = [
        'nombre_rol',
    ];

    /**
     * Relación: Un Rol tiene muchos Usuarios.
     */
    public function usuarios(): HasMany
    {
        // Pasamos: Modelo destino, FK en la tabla destino, Local Key en esta tabla
        return $this->hasMany(User::class, 'id_rol', 'id_rol');
    }
}
