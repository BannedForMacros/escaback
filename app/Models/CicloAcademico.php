<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="CicloAcademico",
 *   required={"anio","semestre"},
 *   @OA\Property(property="id",      type="integer", readOnly=true, example=3),
 *   @OA\Property(property="anio",    type="integer", example=2025),
 *   @OA\Property(property="semestre",type="string",  enum={"I","II"}, example="I")
 * )
 */
class CicloAcademico extends Model
{
    public $timestamps = false;
    protected $fillable = ['anio','semestre'];

    public function contratos()
    {
        return $this->hasMany(ContratoDocente::class, 'ciclo_id');
    }
}
