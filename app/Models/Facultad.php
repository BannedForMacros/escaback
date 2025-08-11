<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Facultad",
 *   required={"siglas"},
 *   @OA\Property(property="id", type="integer", readOnly=true),
 *   @OA\Property(property="siglas", type="string", example="FACFYM"),
 *   @OA\Property(property="nombre", type="string", nullable=true)
 * )
 */
class Facultad extends Model
{
    /** -> aquí la línea importante <- */
    protected $table = 'facultades';

    protected $fillable = ['siglas', 'nombre'];

    /* relaciones */
    public function programas()
    {
        return $this->hasMany(Programa::class);
    }

    public function contratos()
    {
        return $this->hasMany( \App\Models\ContratoDocente::class );
    }

        public function docentes()
    {
        return $this->hasMany(DocenteNombrado::class);
    }
}
