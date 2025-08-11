<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowDocenteNombradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('docente_nombrado', 'id')
            ],
        ];
    }

    /**
     * Permite acceder al valor validado desde el controlador
     * vía $request->validated('id')
     */
    protected function prepareForValidation(): void
    {
        // si el parámetro viene en la ruta, Laravel lo pone en route('id')
        if ($this->route('id')) {
            $this->merge(['id' => $this->route('id')]);
        }
    }
}
