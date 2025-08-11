<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContratoDocenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'categoria'          => $this->categoria,
            'regimen_dedicacion' => $this->regimen_dedicacion,
            'fecha_ingreso'      => $this->fecha_ingreso,
            'fecha_fin'          => $this->fecha_fin,
            'resolucion'         => $this->resolucion,
            'comentario'         => $this->comentario,

            // Relaciones útiles para el frontend
            'ciclo' => [
                'anio'     => $this->ciclo->anio,
                'semestre' => $this->ciclo->semestre,
            ],
            'facultad' => [
                'id'     => $this->facultad->id,
                'siglas' => $this->facultad->siglas,
                'nombre' => $this->facultad->nombre,
            ],
            'programa' => [
                'id'   => $this->programa->id,
                'nombre' => $this->programa->nombre,
            ],
            'tipo_contrato' => $this->tipo?->tipo,   // puede ser null
        ];
    }
}
