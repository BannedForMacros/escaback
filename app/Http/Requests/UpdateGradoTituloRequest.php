<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradoTituloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /* los campos son opcionales, pero mantenemos la regla “uno u otro” */
        return [
            'docente_nombrado_id'   => [
                'sometimes','integer','exists:docente_nombrado,id',
                'prohibits:docente_contratado_id'
            ],
            'docente_contratado_id' => [
                'sometimes','integer','exists:docentes_contratados,id',
                'prohibits:docente_nombrado_id'
            ],

            'mayor_grado_academico' => ['sometimes','string','max:100'],

            'sexo'                  => ['sometimes', Rule::in(['M','F','X'])],
            'nacionalidad'          => ['sometimes','string','max:150'],
            'documento'             => ['sometimes','string','max:150'],
            'numero_documento'      => ['sometimes','string','max:20'],

            'bachiller'             => ['sometimes','string','max:100'],
            'universidad_bach'      => ['sometimes','string','max:100'],
            'pais_bach'             => ['sometimes','string','max:50'],

            'titulo'                => ['sometimes','string','max:150'],
            'universidad_titu'      => ['sometimes','string','max:100'],
            'pais_titu'             => ['sometimes','string','max:50'],
            'resolucion_reconocimiento'=>['sometimes','string','max:500'],

            'maestria'              => ['sometimes','string','max:500'],
            'universidad_maes'      => ['sometimes','string','max:100'],
            'pais_maes'             => ['sometimes','string','max:150'],

            'doctorado'             => ['sometimes','string','max:100'],
            'universidad_doc'       => ['sometimes','string','max:100'],
            'pais_doc'              => ['sometimes','string','max:50'],

            'segunda_especialidad'  => ['sometimes','string','max:500'],
            'universidad_espe'      => ['sometimes','string','max:500'],
            'pais_espe'             => ['sometimes','string','max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'docente_nombrado_id.prohibits'   => 'docente_contratado_id y docente_nombrado_id son mutuamente excluyentes.',
            'docente_contratado_id.prohibits' => 'docente_contratado_id y docente_nombrado_id son mutuamente excluyentes.',
        ];
    }
}
