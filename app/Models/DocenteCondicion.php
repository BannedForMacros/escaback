<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocenteCondicion extends Model
{
    use HasFactory;

    protected $table = 'docente_condiciones';

    protected $fillable = [
        'docente_id',
        'condicion',
        'nro_resolucion',
        'fecha_resolucion',
    ];

    protected $casts = [
        'fecha_resolucion' => 'immutable_date',
    ];

    public function docente()
    {
        return $this->belongsTo(DocenteNombrado::class);
    }
}
