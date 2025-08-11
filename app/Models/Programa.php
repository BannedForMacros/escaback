<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Programa",
 *   required={"facultad_id","nombre"},
 *   @OA\Property(property="id",           type="integer", readOnly=true, example=17),
 *   @OA\Property(property="facultad_id",  type="integer", example=4),
 *   @OA\Property(property="nombre",       type="string",  example="Computación y Electrónica"),
 *   @OA\Property(property="siglas",       type="string",  nullable=true, example="CYE"),
 *   @OA\Property(property="created_at",   type="string", format="date-time", readOnly=true),
 *   @OA\Property(property="updated_at",   type="string", format="date-time", readOnly=true)
 * )
 */
class Programa extends Model
{
    protected $fillable = ['facultad_id', 'nombre', 'siglas'];

    /* relaciones */
    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function contratos()
    {
        return $this->hasMany(ContratoDocente::class);
    }
}
