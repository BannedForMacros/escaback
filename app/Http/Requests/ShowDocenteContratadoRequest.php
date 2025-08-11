<?php
// app/Http/Requests/ShowDocenteContratadoRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowDocenteContratadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Aquí puedes añadir tu propia lógica de autorización
        return true;
    }

    public function rules(): array
    {
        return [
            // Estamos validando que el parámetro `id` exista y sea un entero válido
            'id' => 'required|integer|exists:docentes_contratados,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'El identificador del docente es obligatorio.',
            'id.integer'  => 'El identificador debe ser un número entero.',
            'id.exists'   => 'No existe un docente contratado con ese identificador.',
        ];
    }

    /**
     * Sabemos que el parámetro viene en la ruta, así que
     * decoramos el campo para que FormRequest lo recoja.
     */
    protected function prepareForValidation()
    {
        // Mapea route->id en input->id
        if ($this->route('id')) {
            $this->merge(['id' => $this->route('id')]);
        }
    }
}
