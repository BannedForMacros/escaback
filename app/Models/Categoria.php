<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

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
