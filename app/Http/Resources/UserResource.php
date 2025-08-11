<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   @OA\Property(property="id",             type="integer", example=1),
 *   @OA\Property(property="name",           type="string",  example="Juan Pérez"),
 *   @OA\Property(property="email",          type="string",  format="email", example="juan@ejemplo.com"),
 *   @OA\Property(property="email_verified", type="boolean", example=true),
 *   @OA\Property(property="created_at",     type="string",  format="date-time", example="2025-05-21T12:34:56Z"),
 *   @OA\Property(property="updated_at",     type="string",  format="date-time", example="2025-05-22T08:15:30Z")
 * )
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'email_verified'  => (bool) $this->email_verified_at,
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
        ];
    }
}
