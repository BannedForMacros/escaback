<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="DocenteNombrado",
 *     title="Docente nombrado",
 *     description="Modelo que representa a un docente ordinario nombrado",
 *     required={"dni","apellido_paterno","apellido_materno","nombres"},
 *
 *     @OA\Property(property="id",                      type="integer", example=42),
 *     @OA\Property(property="dni",                     type="string",  example="12345678", description="DNI de 8 dígitos"),
 *     @OA\Property(property="apellido_paterno",        type="string",  example="García"),
 *     @OA\Property(property="apellido_materno",        type="string",  example="Flores"),
 *     @OA\Property(property="nombres",                 type="string",  example="Ana María"),
 *     @OA\Property(property="mayor_grado_academico",   type="string",  example="Magíster"),
 *
 *     @OA\Property(property="categoria_id",            type="integer", nullable=true, example=2,
 *                  description="FK → categorias.id"),
 *     @OA\Property(property="regimen_id",              type="integer", nullable=true, example=1,
 *                  description="FK → regimenes.id"),
 *     @OA\Property(property="regimen_pensionario_id",  type="integer", nullable=true, example=3,
 *                  description="FK → regimenes_pensionarios.id"),
 *     @OA\Property(property="facultad_id",             type="integer", nullable=true, example=5,
 *                  description="FK → facultades.id"),
 *
 *     @OA\Property(property="anio_ingreso_unprg",      type="integer", nullable=true, example=2010),
 *     @OA\Property(property="fecha_nacimiento",        type="string",  format="date", nullable=true, example="1985-07-12"),
 *     @OA\Property(property="direccion",               type="string",  nullable=true, example="Jr. Los Álamos 123 – Chiclayo"),
 *     @OA\Property(property="numero_celular",          type="string",  nullable=true, example="987654321"),
 *     @OA\Property(property="correo_institucional",    type="string",  nullable=true, format="email", example="ana.garcia@unprg.edu.pe"),
 *
 *     @OA\Property(property="created_at",              type="string",  format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at",              type="string",  format="date-time", readOnly=true)
 * )
 */
class DocenteNombrado extends Model
{
    use HasFactory;

    protected $table = 'docente_nombrado';

    protected $fillable = [
        'dni',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'mayor_grado_academico',
        'categoria_id',
        'regimen_id',
        'regimen_pensionario_id',
        'anio_ingreso_unprg',
        'facultad_id',
        'fecha_nacimiento',
        'direccion',
        'numero_celular',
        'correo_institucional',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'immutable_date',
        'created_at'       => 'immutable_datetime',
        'updated_at'       => 'immutable_datetime',
    ];

    /* ─────────────── Relaciones ─────────────── */

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function regimen()
    {
        return $this->belongsTo(Regimen::class);
    }

    public function regimenPensionario()
    {
        return $this->belongsTo(RegimenPensionario::class, 'regimen_pensionario_id');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function historialesCategoriaRegimen()
    {
        return $this->hasMany(DocenteCatRegHist::class, 'docente_id');
    }

    public function condiciones()
    {
        return $this->hasMany(DocenteCondicion::class, 'docente_id');
    }

    public function procedencias()
    {
        return $this->hasMany(DocenteProcedencia::class, 'docente_id');
    }

    public function renacyt()
    {
        return $this->hasOne(Renacyt::class, 'docente_id');
    }
}
