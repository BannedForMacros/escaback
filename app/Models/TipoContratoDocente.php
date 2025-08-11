<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoContratoDocente extends Model
{
    protected $table    = 'tipo_contrato_docente';
    protected $fillable = ['codigo', 'nombre'];

    public function contratos()
    {
        return $this->hasMany(ContratoDocente::class, 'tipo_id');
    }
}
