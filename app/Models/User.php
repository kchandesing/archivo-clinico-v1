<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable;

    // 1. Indicar el nombre exacto de la tabla de tu script SQL
    protected $table = 'usuarios';

    // 2. Definir la llave primaria personalizada
    protected $primaryKey = 'id_usuario';

    // 3. Mapear la columna de fecha_creacion si no usas timestamps nativos (created_at)
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null; // Tu script no contempla fecha_modificacion en usuarios

    // 4. Habilitar la asignación masiva de campos divididos
    protected $fillable = [
        'id_rol',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'activo',
    ];

    // 5. Ocultar campos sensibles en serializaciones
    protected $hidden = [
        'password',
    ];

    // 6. Castear tipos de datos nativos
    protected $casts = [
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relación: Un Usuario pertenece a un Rol.
     */
    public function rol(): BelongsTo
    {
        // Pasamos: Modelo destino, FK en esta tabla, Owner Key en la tabla destino
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }
}
