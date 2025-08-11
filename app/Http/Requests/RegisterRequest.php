<?php
// app/Http/Requests/RegisterRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="RegisterRequest",
 *   type="object",
 *   required={"name","email","password","password_confirmation"},
 *   @OA\Property(property="name",                    type="string", example="Juan Pérez"),
 *   @OA\Property(property="email",                   type="string", format="email", example="juan@ejemplo.com"),
 *   @OA\Property(property="password",                type="string", format="password", example="secret123"),
 *   @OA\Property(property="password_confirmation",   type="string", format="password", example="secret123")
 * )
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                  => 'El nombre es obligatorio.',
            'email.required'                 => 'El correo es obligatorio.',
            'email.email'                    => 'Debe ser un correo válido.',
            'email.unique'                   => 'Este correo ya está registrado.',
            'password.required'              => 'La contraseña es obligatoria.',
            'password.min'                   => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed'             => 'La confirmación de contraseña no coincide.',
            'password_confirmation.required' => 'La confirmación de contraseña es obligatoria.',
        ];
    }
}
