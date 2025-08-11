<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegimenPensionario extends Model
{
    use HasFactory;

    protected $table = 'regimenes_pensionarios';

    protected $fillable = ['descripcion'];

    public function docentes()
    {
        return $this->hasMany(DocenteNombrado::class, 'regimen_pensionario_id');
    }
}
