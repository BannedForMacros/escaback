<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegimenPensionarioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'descripcion' => $this->descripcion,
        ];
    }
}
