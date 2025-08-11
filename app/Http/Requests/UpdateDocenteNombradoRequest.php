<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocenteNombradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;          // aplica tus policies si las tienes
    }

    public function rules(): array
    {
        $integer = ['nullable', 'integer', 'min:1'];

        return [
            /* --- campos simples (DNI no se puede cambiar) --- */
            'apellido_paterno'       => ['sometimes', 'string', 'max:250'],
            'apellido_materno'       => ['sometimes', 'string', 'max:250'],
            'nombres'                => ['sometimes', 'string', 'max:250'],
            'mayor_grado_academico'  => ['sometimes', 'string', 'max:250'],
            'anio_ingreso_unprg'     => ['sometimes', 'integer', 'between:1900,' . now()->year],
            'fecha_nacimiento'       => ['sometimes', 'date', 'before:today'],
            'direccion'              => ['sometimes', 'string', 'max:500'],
            'numero_celular'         => ['sometimes', 'string', 'max:15'],
            'correo_institucional'   => ['sometimes', 'email', 'max:255'],

            /* --- FKs: solo seleccionables (ya deben existir) --- */
            'categoria_id'           => $integer + [Rule::exists('categorias', 'id')],
            'regimen_id'             => $integer + [Rule::exists('regimenes', 'id')],
            'regimen_pensionario_id' => $integer + [Rule::exists('regimenes_pensionarios', 'id')],
            'facultad_id'            => $integer + [Rule::exists('facultades', 'id')],

            /* --- Historial, condiciones, procedencias --- */
            // cada arreglo puede incluir registros existentes (con id) o nuevos (sin id)
            'historiales'   => ['sometimes', 'array'],
            'historiales.*.id'              => ['nullable', 'integer', 'exists:docente_cat_reg_hist,id'],
            'historiales.*.categoria_id'    => ['required', 'integer', 'exists:categorias,id'],
            'historiales.*.regimen_id'      => ['nullable', 'integer', 'exists:regimenes,id'],
            'historiales.*.tipo_resolucion' => ['required', Rule::in(['NOMBRAMIENTO','ASCENSO'])],
            'historiales.*.nro_resolucion'  => ['required', 'string', 'max:100'],
            'historiales.*.fecha_resolucion'=> ['nullable', 'date'],

            'condiciones'   => ['sometimes', 'array'],
            'condiciones.*.id'              => ['nullable','integer','exists:docente_condiciones,id'],
            'condiciones.*.condicion'       => ['required','string','max:120'],
            'condiciones.*.nro_resolucion'  => ['nullable','string','max:100'],
            'condiciones.*.fecha_resolucion'=> ['nullable','date'],

            'procedencias' => ['sometimes', 'array'],
            'procedencias.*.id'             => ['nullable','integer','exists:docente_procedencias,id'],
            'procedencias.*.procedencia'    => ['required','string','max:120'],
            'procedencias.*.nro_resolucion' => ['nullable','string','max:100'],
            'procedencias.*.fecha_resolucion'=>['nullable','date'],
            'procedencias.*.comentario'     => ['nullable','string','max:255'],

            /* --- RENACYT --- */
            'renacyt.nivel_renacyt'         => ['sometimes','string','max:50'],
            'renacyt.nro_resolucion'        => ['nullable','string','max:100'],
        ];
    }
}
