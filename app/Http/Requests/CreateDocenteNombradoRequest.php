<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDocenteNombradoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado.
     * Ajusta la lógica según tus Policies o Gates.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     *
     * ▸ La petición admite dos modos:
     *   1. Importación masiva vía archivo (`file`)
     *   2. Alta individual (campos sueltos)
     *
     * ▸ Para “crear‑si‑no‑existe” se eliminó Rule::exists(); ahora
     *   aceptamos IDs opcionales **o** siglas/‑descripciones para
     *   resolverlos luego en el controlador.
     */
    public function rules(): array
    {
        $integer = ['nullable', 'integer', 'min:1'];

        return [
            /* ─────────── Archivo (importación masiva) ─────────── */
            'file' => [
                'sometimes',
                'file',
                'mimes:xlsx,xls,csv',
            ],

            /* ─────────── Campos individuales ─────────── */
            'dni'   => ['required_without:file', 'digits:8', 'unique:docente_nombrado,dni'],

            'apellido_paterno' => ['required_without:file', 'string', 'max:250'],
            'apellido_materno' => ['required_without:file', 'string', 'max:250'],
            'nombres'          => ['required_without:file', 'string', 'max:250'],

            'mayor_grado_academico' => ['nullable', 'string', 'max:250'],

            /* ──  Catálogos  ──
             *  Se acepta:
             *    • un ID existente   → categoria_id, regimen_id…
             *    • una sigla/descripción para crear → categoria_siglas…
             */
            'categoria_id'           => $integer,
            'categoria_siglas'       => ['nullable', 'string', 'max:10'],

            'regimen_id'             => $integer,
            'regimen_siglas'         => ['nullable', 'string', 'max:10'],

            'regimen_pensionario_id'   => $integer,
            'regimen_pensionario_desc' => ['nullable', 'string', 'max:120'],

            'facultad_id'            => $integer,  // aquí sí debe existir; si quieres crear, agrega lógica similar

            /* ── Datos adicionales ── */
            'anio_ingreso_unprg' => ['nullable', 'integer', 'digits:4', 'between:1900,' . now()->year],
            'fecha_nacimiento'   => ['nullable', 'date', 'before:today'],
            'direccion'          => ['nullable', 'string', 'max:500'],
            'numero_celular'     => ['nullable', 'string', 'max:15'],
            'correo_institucional'=> ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * Mensajes personalizados (opcional).
     */
    public function messages(): array
    {
        return [
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'file.mimes' => 'El archivo debe ser .xlsx, .xls o .csv.',
            'required_without' => 'El campo :attribute es obligatorio cuando no se envía un archivo.',
        ];
    }
}
