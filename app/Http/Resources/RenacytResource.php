<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RenacytResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'nivel_renacyt'  => $this->nivel_renacyt,
            'nro_resolucion' => $this->nro_resolucion,
        ];
    }
}
