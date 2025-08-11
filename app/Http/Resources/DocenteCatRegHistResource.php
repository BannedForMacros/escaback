<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocenteCatRegHistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'tipo_resolucion'  => $this->tipo_resolucion,
            'nro_resolucion'   => $this->nro_resolucion,
            'fecha_resolucion' => optional($this->fecha_resolucion)->format('Y-m-d'),

            'categoria'        => [
                'id'     => $this->categoria->id,
                'desc'   => $this->categoria->descripcion,
            ],
            'regimen'          => $this->regimen ? [
                'id'     => $this->regimen->id,
                'desc'   => $this->regimen->descripcion,
            ] : null,
        ];
    }
}
