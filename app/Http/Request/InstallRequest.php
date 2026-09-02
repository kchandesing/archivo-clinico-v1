<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'master_key'     => 'required|string',
            'db_host'        => 'required|string',
            'db_port'        => 'required|string',
            'db_username'    => 'required|string',
            'db_password'    => 'required|string',
            'database_name'  => 'required|string|alpha_dash|max:63',
            'admin_nombres'  => 'required|string|max:50',
            'admin_paterno'  => 'required|string|max:50',
            'admin_materno'  => 'nullable|string|max:50',
            'admin_email'    => 'required|email|max:100',
            'admin_password' => 'required|string|min:8|confirmed',
        ];
    }
}
