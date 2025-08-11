<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocenteCatRegHist extends Model
{
    use HasFactory;

    protected $table = 'docente_cat_reg_hist';

    protected $fillable = [
        'docente_id',
        'categoria_id',
        'regimen_id',
        'tipo_resolucion',
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

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function regimen()
    {
        return $this->belongsTo(Regimen::class);
    }
}
