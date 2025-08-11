<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * ------------------------------------------------------------------
 *  Esquema OpenAPI
 * ------------------------------------------------------------------
 *
 * @OA\Schema(
 *   title="Grado o Título académico",
 *   description="Registro ligado a un docente nombrado o contratado",
 *
 *   @OA\Property(property="id", type="integer", example=101),
 *
 *   @OA\Property(
 *     property="docente",
 *     type="object",
 *     description="Datos mínimos del docente asociado",
 *     @OA\Property(property="id",   type="integer", example=42),
 *     @OA\Property(property="tipo", type="string",  enum={"nombrado","contratado"}, example="nombrado"),
 *     @OA\Property(property="dni",  type="string",  example="12345678"),
 *     @OA\Property(property="nombre_completo", type="string", example="GARCÍA FLORES, ANA MARÍA")
 *   ),
 *
 *   @OA\Property(property="mayor_grado_academico", type="string", example="Doctor"),
 *   @OA\Property(property="bachiller",             type="string", nullable=true,  example="Bachiller en Física"),
 *   @OA\Property(property="universidad_bach",      type="string", nullable=true,  example="UNPRG"),
 *   @OA\Property(property="pais_bach",             type="string", nullable=true,  example="Perú"),
 *
 *   @OA\Property(property="titulo",                type="string", nullable=true,  example="Licenciado en Física"),
 *   @OA\Property(property="universidad_titu",      type="string", nullable=true,  example="UNPRG"),
 *   @OA\Property(property="pais_titu",             type="string", nullable=true,  example="Perú"),
 *   @OA\Property(property="resolucion_reconocimiento", type="string", nullable=true, example="R‑2025‑001"),
 *
 *   @OA\Property(property="maestria",              type="string", nullable=true,  example="Magíster en Docencia"),
 *   @OA\Property(property="universidad_maes",      type="string", nullable=true,  example="UNPRG"),
 *   @OA\Property(property="pais_maes",             type="string", nullable=true,  example="Perú"),
 *
 *   @OA\Property(property="doctorado",             type="string", nullable=true,  example="Doctorado en Educación"),
 *   @OA\Property(property="universidad_doc",       type="string", nullable=true,  example="PUCP"),
 *   @OA\Property(property="pais_doc",             type="string", nullable=true,  example="Perú"),
 *
 *   @OA\Property(property="segunda_especialidad",  type="string", nullable=true,  example="Especialidad en Matemáticas"),
 *   @OA\Property(property="universidad_espe",      type="string", nullable=true,  example="UNMSM"),
 *   @OA\Property(property="pais_espe",             type="string", nullable=true,  example="Perú"),
 *
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-24T12:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-25T09:30:00Z")
 * )
 */
class GradosTitulosResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string,mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,

            /* ---------- datos del docente (via accessor) ---------- */
            'docente' => [
                'id'              => $this->docenteNombrado?->id
                                     ?? $this->docenteContratado?->id,
                'tipo'            => $this->docenteNombrado ? 'nombrado' : 'contratado',
                'dni'             => $this->dni,
                'nombre_completo' => $this->nombre_completo,
            ],

            /* ---------- información académica --------------------- */
            'mayor_grado_academico' => $this->mayor_grado_academico,

            'bachiller'             => $this->bachiller,
            'universidad_bach'      => $this->universidad_bach,
            'pais_bach'             => $this->pais_bach,

            'titulo'                => $this->titulo,
            'universidad_titu'      => $this->universidad_titu,
            'pais_titu'             => $this->pais_titu,
            'resolucion_reconocimiento' => $this->resolucion_reconocimiento,

            'maestria'              => $this->maestria,
            'universidad_maes'      => $this->universidad_maes,
            'pais_maes'             => $this->pais_maes,

            'doctorado'             => $this->doctorado,
            'universidad_doc'       => $this->universidad_doc,
            'pais_doc'              => $this->pais_doc,

            'segunda_especialidad'  => $this->segunda_especialidad,
            'universidad_espe'      => $this->universidad_espe,
            'pais_espe'             => $this->pais_espe,

            /* ---------- marcas de tiempo -------------------------- */
            'created_at'            => $this->created_at?->toIso8601String(),
            'updated_at'            => $this->updated_at?->toIso8601String(),
        ];
    }
}
