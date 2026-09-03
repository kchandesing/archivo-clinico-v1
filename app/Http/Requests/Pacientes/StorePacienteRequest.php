<?php

namespace App\Http\Requests\Pacientes;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
    /**
     * Autoriza de forma global que el personal autenticado ejecute esta acción.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define las reglas de validación acopladas a los límites de tu script SQL.
     */
    public function rules(): array
    {
        return [
            // 1. DATOS CORE DEL PACIENTE
            'es_provisional'    => 'nullable|boolean',
            
            // Si el request trae la variable 'es_provisional', el CURP puede ser null, si no, es obligatorio
            'curp'              => $this->has('es_provisional') ? 'nullable' : [
                'required',
                'string',
                'size:18',
                'unique:pacientes,curp',
                'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9]{2}$/'
            ],
            'nombres'           => 'required|string|max:50', // Acoplado a tu cambio VARCHAR(50)
            'apellido_paterno'  => 'required|string|max:50',
            'apellido_materno'  => 'nullable|string|max:50',
            'fecha_nacimiento'  => 'required|date|before_or_equal:today',
            'sexo'              => 'required|string|in:H,M,O', // Hombre, Mujer, Otro

            // 2. DATOS DOMICILIARIOS (Tabla: direcciones_pacientes)
            'calle'             => 'required|string|max:100',
            'numero_exterior'   => 'required|string|max:10',
            'numero_interior'   => 'nullable|string|max:10',
            'colonia'           => 'required|string|max:100',
            'codigo_postal'     => 'required|string|size:5|regex:/^[0-9]{5}$/',
            'localidad'         => 'required|string|max:100',
            'municipio'         => 'required|string|max:100',
            'estado'            => 'required|string|max:50',

            // 3. UBICACIÓN EN ARCHIVO FISICO (Tabla: localizaciones_fisicas - Opcionales)
            'pasillo'           => 'nullable|string|max:50',
            'estante'           => 'nullable|string|max:50',
            'caja'              => 'nullable|string|max:50',
        ];
    }

    /**
     * Personaliza los mensajes de error para que la interfaz los muestre en español limpio.
     */
    public function messages(): array
    {
        return [
            'curp.required' => 'El campo CURP es obligatorio para expedientes definitivos.',
            'curp.size'     => 'El CURP debe tener exactamente 18 caracteres.',
            'curp.unique'   => 'Este CURP ya se encuentra registrado.',
            'curp.regex'    => 'El formato del CURP ingresado no es válido.',
            'codigo_postal.size' => 'El código postal debe tener exactamente 5 dígitos.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser una fecha futura.',
        ];
    }
}
