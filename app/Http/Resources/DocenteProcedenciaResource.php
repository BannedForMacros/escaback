<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocenteProcedenciaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'procedencia'      => $this->procedencia,
            'nro_resolucion'   => $this->nro_resolucion,
            'fecha_resolucion' => optional($this->fecha_resolucion)->format('Y-m-d'),
            'comentario'       => $this->comentario,
        ];
    }
}
