<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocenteProcedencia extends Model
{
    use HasFactory;

    protected $table = 'docente_procedencias';

    protected $fillable = [
        'docente_id',
        'procedencia',
        'nro_resolucion',
        'fecha_resolucion',
        'comentario',
    ];

    protected $casts = [
        'fecha_resolucion' => 'immutable_date',
    ];

    public function docente()
    {
        return $this->belongsTo(DocenteNombrado::class);
    }
}
