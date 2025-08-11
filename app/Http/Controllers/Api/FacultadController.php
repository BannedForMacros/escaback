<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacultadResource;
use App\Models\Facultad;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class FacultadController extends Controller
{
    /**
     * @OA\Get(
     *   path="/facultades",
     *   tags={"Catálogos"},
     *   summary="Lista completa de facultades",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *   )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(
            FacultadResource::collection(
                Facultad::orderBy('siglas')->get()
            )
        );
    }
}
