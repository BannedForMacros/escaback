<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocenteContratadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ───── datos personales ─────
            'id'                    => $this->id,
            'dni'                   => $this->dni,
            'apellido_paterno'      => $this->apellido_paterno,
            'apellido_materno'      => $this->apellido_materno,
            'nombres'               => $this->nombres,
            'sexo'                  => $this->sexo,
            'mayor_grado_academico' => $this->mayor_grado_academico,
            'correo'                => $this->correo,
            'telefono'              => $this->telefono,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,

            // ─── contratos (se incluye solo si ya viene eager-loaded) ───
            'contratos' => ContratoDocenteResource::collection(
                $this->whenLoaded('contratos')
            ),
            'tiempo_servicio' => $this->tiempo_servicio['texto'],
        ];
    }
}
