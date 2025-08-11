<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class CategoriaController extends Controller
{
    /**
     * @OA\Get(
     *   path="/categorias",
     *   tags={"Catálogos"},
     *   summary="Lista de categorías docentes",
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
            CategoriaResource::collection(
                Categoria::orderBy('siglas')->get()
            )
        );
    }
}
