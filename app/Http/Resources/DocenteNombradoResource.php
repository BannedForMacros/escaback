<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocenteNombradoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            /* ─── Datos básicos ─────────────────────────────── */
            'id'                    => $this->id,
            'dni'                   => $this->dni,
            'apellido_paterno'      => $this->apellido_paterno,
            'apellido_materno'      => $this->apellido_materno,
            'nombres'               => $this->nombres,
            'mayor_grado_academico' => $this->mayor_grado_academico,

            /* ─── Relaciones 1‑a‑1 (FK actuales) ───────────── */
            'categoria'           => new CategoriaResource($this->whenLoaded('categoria')),
            'regimen'             => new RegimenResource($this->whenLoaded('regimen')),
            'regimen_pensionario' => new RegimenPensionarioResource($this->whenLoaded('regimenPensionario')),
            'facultad'            => new FacultadResource($this->whenLoaded('facultad')),

            /* ─── Otros campos de la tabla principal ───────── */
            'anio_ingreso_unprg' => $this->anio_ingreso_unprg,
            'fecha_nacimiento'   => optional($this->fecha_nacimiento)->format('Y-m-d'),
            'direccion'          => $this->direccion,
            'numero_celular'     => $this->numero_celular,
            'correo_institucional'=> $this->correo_institucional,

            /* ─── Listas dependientes (para edición) ───────── */
            'historiales_categoria_regimen' =>
                DocenteCatRegHistResource::collection(
                    $this->whenLoaded('historialesCategoriaRegimen')
                ),

            'condiciones'  =>
                DocenteCondicionResource::collection($this->whenLoaded('condiciones')),

            'procedencias' =>
                DocenteProcedenciaResource::collection($this->whenLoaded('procedencias')),

            'renacyt'      => new RenacytResource($this->whenLoaded('renacyt')),

            /* ─── Timestamps ───────────────────────────────── */
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
