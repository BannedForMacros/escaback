<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * Datos que exige el endpoint de importación.
 *
 * @OA\Schema(
 *   schema="ImportContratosRequest",
 *   required={"anio","semestre","file"},
 *   @OA\Property(property="anio",     type="integer", example=2025),
 *   @OA\Property(property="semestre", type="string", enum={"I","II"}, example="I"),
 *   @OA\Property(
 *     property="file",
 *     type="string",
 *     format="binary",
 *     description="Archivo XLSX/XLS/CSV"
 *   )
 * )
 */
class ImportContratosRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'anio'     => ['required','digits:4','integer','min:1900'],
            'semestre' => ['required','in:I,II'],
            'file'     => ['required','file','mimes:xlsx,xls,csv'],
        ];
    }
}
