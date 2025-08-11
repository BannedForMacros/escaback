<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegimenResource;
use App\Models\Regimen;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class RegimenController extends Controller
{
    /**
     * @OA\Get(
     *   path="/regimenes",
     *   tags={"Catálogos"},
     *   summary="Lista completa de regímenes de dedicación",
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
            RegimenResource::collection(
                Regimen::orderBy('siglas')->get()
            )
        );
    }
}
