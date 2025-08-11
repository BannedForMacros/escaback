<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renacyt extends Model
{
    use HasFactory;

    protected $table = 'renacyt';
    protected $primaryKey = 'docente_id';   // clave PK y FK a la vez
    public $incrementing = false;

    protected $fillable = [
        'docente_id',
        'nivel_renacyt',
        'nro_resolucion',
    ];

    public function docente()
    {
        return $this->belongsTo(DocenteNombrado::class, 'docente_id');
    }
}
