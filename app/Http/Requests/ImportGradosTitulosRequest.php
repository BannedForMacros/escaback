<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportGradosTitulosRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file'                    => ['nullable', 'file', 'mimes:xlsx,xls,csv'],
            'docente_id'              => ['nullable', 'integer', 'exists:docentes,id'],
            'apellido_paterno'        => ['nullable', 'string', 'max:100'],
            'apellido_materno'        => ['nullable', 'string', 'max:100'],
            'nombres'                 => ['nullable', 'string', 'max:150'],
            'sexo'                    => ['nullable', 'in:M,F'],
            'nacionalidad'            => ['nullable', 'string', 'max:50'],
            'documento'               => ['nullable', 'string', 'max:50'],
            'numero_documento'        => ['nullable', 'string', 'max:20'],
            'mayor_grado_academico'   => ['nullable', 'string', 'max:100'],
            'bachiller'               => ['nullable', 'string', 'max:100'],
            'universidad_bach'        => ['nullable', 'string', 'max:100'],
            'pais_bach'               => ['nullable', 'string', 'max:50'],
            'titulo'                  => ['nullable', 'string', 'max:150'],
            'universidad_titu'        => ['nullable', 'string', 'max:100'],
            'pais_titu'               => ['nullable', 'string', 'max:50'],
            'resolucion_reconocimiento'=> ['nullable', 'string', 'max:100'],
            'maestria'                => ['nullable', 'string', 'max:100'],
            'universidad_maes'        => ['nullable', 'string', 'max:100'],
            'pais_maes'               => ['nullable', 'string', 'max:50'],
            'doctorado'               => ['nullable', 'string', 'max:100'],
            'universidad_doc'         => ['nullable', 'string', 'max:100'],
            'pais_doc'                => ['nullable', 'string', 'max:50'],
            'segunda_especialidad'    => ['nullable', 'string', 'max:100'],
            'universidad_espe'        => ['nullable', 'string', 'max:100'],
            'pais_espe'               => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages()
    {
        return [
            'file.mimetypes'          => 'El archivo debe ser .xlsx, .xls o .csv.',
            'docente_id.exists'       => 'El docente seleccionado no existe.',
        ];
    }
}
