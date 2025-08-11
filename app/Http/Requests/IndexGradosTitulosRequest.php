<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexGradosTitulosRequest extends FormRequest
{
    public function authorize()
    {
        // Ajusta según tu lógica de permisos
        return true;
    }

    public function rules()
    {
        return [
            'page'       => ['sometimes', 'integer', 'min:1'],
            'per_page'   => ['sometimes', 'integer', 'min:1', 'max:100'],
            'docente_id' => ['sometimes', 'integer', 'exists:docentes,id'],
            'search'     => ['sometimes', 'string', 'max:150'],
        ];
    }

    public function messages()
    {
        return [
            'page.integer'       => 'El parámetro page debe ser un número entero.',
            'page.min'           => 'El parámetro page debe ser al menos 1.',
            'per_page.integer'   => 'El parámetro per_page debe ser un número entero.',
            'per_page.min'       => 'El parámetro per_page debe ser al menos 1.',
            'per_page.max'       => 'per_page no puede exceder 100.',
            'docente_id.integer' => 'El docente_id debe ser un número entero.',
            'docente_id.exists'  => 'El docente indicado no existe.',
            'search.string'      => 'El filtro de búsqueda debe ser texto.',
            'search.max'         => 'El filtro de búsqueda no puede exceder 150 caracteres.',
        ];
    }
}
