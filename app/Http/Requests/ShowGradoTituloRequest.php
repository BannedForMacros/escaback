<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowGradoTituloRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Antes de validar, inyectamos el parámetro de ruta 'id'
     * en el conjunto de datos que valida el FormRequest.
     */
    public function validationData()
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }

    public function rules()
    {
        return [
            'id' => ['required', 'integer', 'exists:grados_titulos,id'],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Debes indicar el ID del registro de grado/título.',
            'id.integer'  => 'El ID debe ser un número entero.',
            'id.exists'   => 'El registro de Grado/Título no existe.',
        ];
    }
}
