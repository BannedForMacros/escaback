<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * ----------------------------------------------------------------------------
 *  Form Request: IndexDocentesContratadosRequest
 * ----------------------------------------------------------------------------
 *
 *  Se usa en el método index() del controlador
 *  DocenteContratadoController para validar los parámetros de consulta.
 *
 *  @OA\Schema(
 *    schema="IndexDocentesContratadosQuery",
 *    description="Parámetros de consulta para listar docentes contratados",
 *
 *    @OA\Property(
 *      property="search",
 *      type="string",
 *      maxLength=100,
 *      description="Texto a buscar en DNI, nombres, apellidos o resolución"
 *    ),
 *    @OA\Property(
 *      property="page",
 *      type="integer",
 *      minimum=1,
 *      example=1,
 *      description="Número de página"
 *    ),
 *    @OA\Property(
 *      property="per_page",
 *      type="integer",
 *      minimum=1,
 *      maximum=100,
 *      example=25,
 *      description="Registros por página"
 *    )
 *  )
 */
class IndexDocentesContratadosRequest extends FormRequest
{
    /**
     *  El acceso ya está protegido por el middleware de autenticación,
     *  así que simplemente devolvemos true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *  Reglas de validación para ?search, ?page y ?per_page.
     */
    public function rules(): array
    {
        return [
            'search'   => ['sometimes', 'string', 'max:100'],
            'page'     => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }

    /**
     *  Mensajes opcionales y personalizados.
     */
    public function messages(): array
    {
        return [
            'per_page.between' => 'El campo per_page debe estar entre 1 y 100.',
        ];
    }
}
