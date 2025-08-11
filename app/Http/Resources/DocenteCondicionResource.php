<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocenteCondicionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'condicion'        => $this->condicion,
            'nro_resolucion'   => $this->nro_resolucion,
            'fecha_resolucion' => optional($this->fecha_resolucion)->format('Y-m-d'),
        ];
    }
}
