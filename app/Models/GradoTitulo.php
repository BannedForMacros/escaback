<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="GradoTitulo",
 *   title="Grado o Título",
 *   description="Registro académico ligado a un docente (nombrado o contratado)",
 *   required={"mayor_grado_academico"},
 *
 *   @OA\Property(property="id", type="integer", example=101),
 *
 *   @OA\Property(property="docente", type="object",
 *     @OA\Property(property="id",   type="integer", example=42),
 *     @OA\Property(property="tipo", type="string",  enum={"nombrado","contratado"}),
 *     @OA\Property(property="dni",  type="string",  example="12345678"),
 *     @OA\Property(property="nombre_completo", type="string", example="GARCÍA FLORES, ANA MARÍA")
 *   ),
 *
 *   @OA\Property(property="mayor_grado_academico", type="string", example="Doctor"),
 *   @OA\Property(property="titulo",                 type="string", example="Licenciado en Física"),
 *   @OA\Property(property="maestria",               type="string", example="Magíster en Docencia"),
 *   @OA\Property(property="doctorado",              type="string", example="Doctorado en Educación"),
 *   @OA\Property(property="segunda_especialidad",   type="string", example="Especialidad en Matemáticas"),
 *
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class GradoTitulo extends Model
{
    use HasFactory;

    protected $table = 'grados_titulos';

    protected $fillable = [
        'docente_nombrado_id',
        'docente_contratado_id',

        'sexo',
        'nacionalidad',
        'documento',
        'numero_documento',
        'mayor_grado_academico',

        'bachiller',
        'universidad_bach',
        'pais_bach',

        'titulo',
        'universidad_titu',
        'pais_titu',
        'resolucion_reconocimiento',

        'maestria',
        'universidad_maes',
        'pais_maes',

        'doctorado',
        'universidad_doc',
        'pais_doc',

        'segunda_especialidad',
        'universidad_espe',
        'pais_espe',
    ];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /* ─────────── Relaciones ─────────── */

    public function docenteNombrado()
    {
        return $this->belongsTo(DocenteNombrado::class, 'docente_nombrado_id');
    }

    public function docenteContratado()
    {
        return $this->belongsTo(DocenteContratado::class, 'docente_contratado_id');
    }

    /* ─────────── Accesores comodín ─────────── */

    public function getDniAttribute(): ?string
    {
        return optional($this->docenteNombrado ?? $this->docenteContratado)->dni;
    }

    public function getNombreCompletoAttribute(): ?string
    {
        $d = $this->docenteNombrado ?? $this->docenteContratado;
        return $d
            ? "{$d->apellido_paterno} {$d->apellido_materno}, {$d->nombres}"
            : null;
    }
}
