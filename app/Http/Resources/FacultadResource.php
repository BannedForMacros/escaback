<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FacultadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'siglas'  => $this->siglas,
            'nombre'  => $this->nombre,
        ];
    }
}
