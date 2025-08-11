<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexDocentesNombradosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'search'   => ['nullable', 'string', 'max:255'],
        ];
    }
}
