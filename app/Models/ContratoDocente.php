<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="ContratoDocente",
 *   required={"docente_id","ciclo_id"},
 *
 *   @OA\Property(property="id",               type="integer", readOnly=true),
 *   @OA\Property(property="docente_id",       type="integer"),
 *   @OA\Property(property="ciclo_id",         type="integer"),
 *   @OA\Property(property="categoria",        type="string",  nullable=true),
 *   @OA\Property(property="regimen_dedicacion", type="string", nullable=true),
 *   @OA\Property(property="fecha_ingreso",    type="string",  format="date", nullable=true),
 *   @OA\Property(property="fecha_fin",        type="string",  format="date", nullable=true),
 *   @OA\Property(property="resolucion",       type="string",  nullable=true),
 *   @OA\Property(property="facultad_id",      type="integer"),
 *   @OA\Property(property="programa_id",      type="integer"),
 *   @OA\Property(property="tipo_id",          type="integer", nullable=true, description="FK a tipo_contrato_docente"),
 *   @OA\Property(property="comentario",       type="string",  nullable=true, maxLength=255),
 *
 *   @OA\Property(property="created_at",       type="string", format="date-time", readOnly=true),
 *   @OA\Property(property="updated_at",       type="string", format="date-time", readOnly=true)
 * )
 */
class ContratoDocente extends Model
{
    protected $table = 'contratos_docente';

    protected $fillable = [
        'docente_id',
        'ciclo_id',
        'categoria',
        'regimen_dedicacion',
        'fecha_ingreso',
        'fecha_fin',
        'resolucion',
        'facultad_id',
        'programa_id',
        'tipo_id',
        'comentario',
    ];

    /* ---------- Relaciones ---------- */
    public function tipo()     { return $this->belongsTo(TipoContratoDocente::class, 'tipo_id'); }
    public function docente()  { return $this->belongsTo(DocenteContratado::class); }
    public function ciclo()    { return $this->belongsTo(CicloAcademico::class); }
    public function facultad() { return $this->belongsTo(Facultad::class); }
    public function programa() { return $this->belongsTo(Programa::class); }
}
