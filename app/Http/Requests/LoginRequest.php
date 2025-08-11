<?php
// app/Http/Requests/LoginRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="LoginRequest",
 *   type="object",
 *   required={"email","password"},
 *   @OA\Property(
 *     property="email",
 *     type="string",
 *     format="email",
 *     example="usuario@ejemplo.com"
 *   ),
 *   @OA\Property(
 *     property="password",
 *     type="string",
 *     format="password",
 *     example="secret123"
 *   )
 * )
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'Debe ser un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos :min caracteres.',
        ];
    }
}
