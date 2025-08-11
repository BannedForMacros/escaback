<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradoTituloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // aplica tus policies si corresponde
    }

    /** Si viene archivo, anulamos los demás campos (importación masiva). */
    protected function prepareForValidation(): void
    {
        if ($this->hasFile('file')) {
            $this->replace(['file' => $this->file('file')]); // borra resto
        }
    }

    public function rules(): array
    {
        /* importación masiva ----------------------------- */
        if ($this->hasFile('file')) {
            return [
                'file' => ['required','file','mimes:xlsx,xls,csv']
            ];
        }

        /* creación individual ---------------------------- */
        return [
            /* EXACTAMENTE uno de los dos FK */
            'docente_nombrado_id'   => [
                'required_without:docente_contratado_id',
                'prohibits:docente_contratado_id',
                'nullable','integer','exists:docente_nombrado,id'
            ],
            'docente_contratado_id' => [
                'required_without:docente_nombrado_id',
                'prohibits:docente_nombrado_id',
                'nullable','integer','exists:docentes_contratados,id'
            ],

            'mayor_grado_academico' => ['required','string','max:100'],

            'sexo'                  => ['nullable', Rule::in(['M','F','X'])],
            'nacionalidad'          => ['nullable','string','max:150'],
            'documento'             => ['nullable','string','max:150'],
            'numero_documento'      => ['nullable','string','max:20'],

            'bachiller'             => ['nullable','string','max:100'],
            'universidad_bach'      => ['nullable','string','max:100'],
            'pais_bach'             => ['nullable','string','max:50'],

            'titulo'                => ['nullable','string','max:150'],
            'universidad_titu'      => ['nullable','string','max:100'],
            'pais_titu'             => ['nullable','string','max:50'],
            'resolucion_reconocimiento'=>['nullable','string','max:500'],

            'maestria'              => ['nullable','string','max:500'],
            'universidad_maes'      => ['nullable','string','max:100'],
            'pais_maes'             => ['nullable','string','max:150'],

            'doctorado'             => ['nullable','string','max:100'],
            'universidad_doc'       => ['nullable','string','max:100'],
            'pais_doc'              => ['nullable','string','max:50'],

            'segunda_especialidad'  => ['nullable','string','max:500'],
            'universidad_espe'      => ['nullable','string','max:500'],
            'pais_espe'             => ['nullable','string','max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'El archivo debe ser XLSX, XLS o CSV.',

            'docente_nombrado_id.required_without'   =>
                'Debe indicar un docente nombrado o un docente contratado.',
            'docente_contratado_id.required_without' =>
                'Debe indicar un docente nombrado o un docente contratado.',
            'docente_nombrado_id.prohibits'   => 'No puede enviar ambos docentes a la vez.',
            'docente_contratado_id.prohibits' => 'No puede enviar ambos docentes a la vez.',

            'mayor_grado_academico.required' => 'Debe indicar el mayor grado académico.'
        ];
    }
}
