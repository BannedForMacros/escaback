<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'siglas'     => $this->siglas,
            'descripcion'=> $this->descripcion,
        ];
    }
}
