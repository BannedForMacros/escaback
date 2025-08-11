<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradosTitulosRequest extends FormRequest
{
    public function authorize()
    {
        // Ajusta según tu lógica de permisos; por defecto permitimos
        return true;
    }

    protected function prepareForValidation()
    {
        // Si enviaron un archivo, removemos los demás campos para no romper las reglas condicionales.
        if ($this->hasFile('file')) {
            // Forzamos a que todos los demás campos se consideren nulos/vacíos
            $this->merge([
                'dni'                      => null,
                'mayor_grado_academico'    => null,
                'categoria_regimen_001'    => null,
                'resolucion_nombramiento'  => null,
                'categoria_regimen_002'    => null,
                'categoria_regimen_003'    => null,
                // … cualquier otro campo de grados/títulos …
            ]);
        }
    }

    public function rules()
    {
        return [
            // Si vino archivo, el file es requerido y debe ser xlsx/xls/csv
            'file' => [
                'nullable',
                'file',
                'mimes:xlsx,xls,csv',
            ],

            // Los siguientes campos sólo valen si NO vino archivo
            'dni' => [
                Rule::requiredIf(function () {
                    return !$this->hasFile('file');
                }),
                'string',
                'exists:docentes,dni',
            ],
            'mayor_grado_academico' => [
                Rule::requiredIf(function () {
                    return !$this->hasFile('file');
                }),
                'string',
                'max:100',
            ],
            'categoria_regimen_001' => [
                Rule::requiredIf(function () {
                    return !$this->hasFile('file');
                }),
                'string',
                'max:50',
            ],
            'resolucion_nombramiento' => [
                Rule::nullable(), // opcional extra
                'string',
                'max:100',
            ],
            'categoria_regimen_002' => [
                'nullable',
                'string',
                'max:50',
            ],
            'categoria_regimen_003' => [
                'nullable',
                'string',
                'max:50',
            ],
            // Si necesitas más campos para maestría, doctorado, etc., añádelos aquí:
            // 'campo_excel_adicional' => ['nullable','string','max:100'],
        ];
    }

    public function messages()
    {
        return [
            'file.mimes' => 'El archivo debe ser XLSX, XLS o CSV.',
            'dni.required' => 'Debe indicar el DNI del docente.',
            'dni.exists'   => 'No se encontró ningún docente con ese DNI.',
            'mayor_grado_academico.required' => 'Debe indicar el Mayor Grado Académico.',
            'categoria_regimen_001.required' => 'Debe indicar al menos el campo “categoria_regimen_001” (por ejemplo: Bachiller).',
            // …
        ];
    }
}
