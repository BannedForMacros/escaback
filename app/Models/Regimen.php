<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regimen extends Model
{
    use HasFactory;
    protected $table = 'regimenes';

    protected $fillable = ['siglas', 'descripcion'];

    public function docentes()
    {
        return $this->hasMany(DocenteNombrado::class);
    }

    public function historiales()
    {
        return $this->hasMany(DocenteCatRegHist::class);
    }
}
