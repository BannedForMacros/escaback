<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{
    IndexGradosTitulosRequest,
    ShowGradoTituloRequest,
    StoreGradoTituloRequest,
    UpdateGradoTituloRequest
};
use App\Http\Resources\GradosTitulosResource;
use App\Models\GradoTitulo;
use App\Imports\GradosTitulosImport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;

class GradoTituloController extends Controller
{
    /* ───────────────────────────────────────────────────────────── */
    /* 1. INDEX                                                     */
    /* ───────────────────────────────────────────────────────────── */

/**
 * @OA\Get(
 *   path="/grados-titulos",
 *   tags={"GradosTitulos"},
 *   summary="Lista paginada de grados/títulos",
 *   security={{"bearerAuth":{}}},
 *
 *   @OA\Parameter(
 *     name="page", in="query",
 *     @OA\Schema(type="integer", example=1)
 *   ),
 *   @OA\Parameter(
 *     name="per_page", in="query",
 *     @OA\Schema(type="integer", example=25)
 *   ),
 *   @OA\Parameter(
 *     name="docente_id", in="query",
 *     description="Filtra por *cualquier* docente (nombrado o contratado)",
 *     @OA\Schema(type="integer", example=42)
 *   ),
 *   @OA\Parameter(
 *     name="docente_nombrado_id", in="query",
 *     description="Filtra sólo por docente nombrado",
 *     @OA\Schema(type="integer", example=42)
 *   ),
 *   @OA\Parameter(
 *     name="docente_contratado_id", in="query",
 *     description="Filtra sólo por docente contratado",
 *     @OA\Schema(type="integer", example=42)
 *   ),
 *   @OA\Parameter(
 *     name="docente_tipo", in="query",
 *     description="Filtra por tipo de docente (nombrado | contratado)",
 *     @OA\Schema(type="string", enum={"nombrado","contratado"})
 *   ),
 *   @OA\Parameter(
 *     name="search", in="query",
 *     description="Busca por título, maestría, doctorado, etc.",
 *     @OA\Schema(type="string", example="Doctor")
 *   ),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Colección paginada",
 *     @OA\JsonContent(
 *       @OA\Property(
 *         property="data", type="array",
 *         @OA\Items(ref="#/components/schemas/GradoTitulo")
 *       ),
 *       @OA\Property(
 *         property="meta", type="object",
 *         @OA\Property(property="current_page", type="integer"),
 *         @OA\Property(property="last_page",    type="integer"),
 *         @OA\Property(property="per_page",     type="integer"),
 *         @OA\Property(property="total",        type="integer")
 *       )
 *     )
 *   )
 * )
 */
public function index(IndexGradosTitulosRequest $request): JsonResponse
{
    $perPage    = $request->input('per_page', 25);
    $page       = $request->input('page', 1);
    $docId      = $request->input('docente_id');
    $docNomId   = $request->input('docente_nombrado_id');
    $docConId   = $request->input('docente_contratado_id');
    $docTipo    = $request->input('docente_tipo'); // nombrado | contratado
    $search     = $request->input('search');

    $query = GradoTitulo::with(['docenteNombrado','docenteContratado']);

    // 1) Filtros específicos (prioridad)
    if ($docNomId || $docConId) {
        $query->where(function($q) use ($docNomId, $docConId) {
            if ($docNomId) {
                $q->where('docente_nombrado_id', $docNomId);
            }
            if ($docConId) {
                $q->orWhere('docente_contratado_id', $docConId);
            }
        });
    }
    // 2) Filtro genérico si no hay filtros específicos
    elseif ($docId) {
        $query->where(function($q) use ($docId) {
            $q->where('docente_nombrado_id',   $docId)
              ->orWhere('docente_contratado_id', $docId);
        });
    }

    // 3) Filtrado por tipo de docente
    if ($docTipo === 'nombrado') {
        $query->whereNotNull('docente_nombrado_id');
    } elseif ($docTipo === 'contratado') {
        $query->whereNotNull('docente_contratado_id');
    }

    // 4) Búsqueda libre en cualquiera de los campos académicos
    if ($search) {
        $term = '%'.strtolower($search).'%';
        $query->where(function($q) use ($term) {
            $q->whereRaw('LOWER(mayor_grado_academico) LIKE ?', [$term])
              ->orWhereRaw('LOWER(titulo) LIKE ?',               [$term])
              ->orWhereRaw('LOWER(maestria) LIKE ?',             [$term])
              ->orWhereRaw('LOWER(doctorado) LIKE ?',            [$term])
              ->orWhereRaw('LOWER(segunda_especialidad) LIKE ?', [$term]);
        });
    }

    $collection = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'data' => GradosTitulosResource::collection($collection),
        'meta' => [
            'current_page' => $collection->currentPage(),
            'last_page'    => $collection->lastPage(),
            'per_page'     => $collection->perPage(),
            'total'        => $collection->total(),
        ],
    ], Response::HTTP_OK);
}


    /* ───────────────────────────────────────────────────────────── */
    /* 2. SHOW                                                      */
    /* ───────────────────────────────────────────────────────────── */

    /**
     * @OA\Get(
     *   path="/grados-titulos/{id}",
     *   tags={"GradosTitulos"},
     *   summary="Detalle de un grado/título",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer", example=101)),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/GradoTitulo")),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(ShowGradoTituloRequest $req, int $id): JsonResponse
    {
        $gt = GradoTitulo::with(['docenteNombrado','docenteContratado'])
              ->findOrFail($id);

        return (new GradosTitulosResource($gt))
               ->response()->setStatusCode(Response::HTTP_OK);
    }

    /* ───────────────────────────────────────────────────────────── */
    /* 3. STORE (importación ∨ creación individual)                 */
    /* ───────────────────────────────────────────────────────────── */

    /**
     * @OA\Post(
     *   path="/grados-titulos",
     *   tags={"GradosTitulos"},
     *   summary="Importar archivo o crear un registro manualmente",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     description="Archivo XLSX/XLS/CSV **o** JSON con campos académicos y el ID del docente.",
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="file", type="file", description="Plantilla de importación masiva")
     *       )
     *     ),
     *     @OA\JsonContent(
     *       type="object",
     *       oneOf={
     *         @OA\Schema(                       
     *           @OA\Property(property="mayor_grado_academico", type="string",  example="Doctor"),
     *           @OA\Property(property="titulo",                type="string",  example="Licenciado en Física")
     *         ),
     *         @OA\Schema(                     
     *           @OA\Property(property="file", type="string", format="binary")
     *         )
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="Creado",
     *     @OA\JsonContent(ref="#/components/schemas/GradoTitulo")),
     *   @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(StoreGradoTituloRequest $request): JsonResponse
    {
        /* -------- Importación masiva --------------------- */
        if ($request->hasFile('file')) {
            Excel::import(new GradosTitulosImport, $request->file('file'));

            return response()->json([
                'message' => 'Importación completada correctamente'
            ], Response::HTTP_OK);
        }

        /* -------- Creación individual -------------------- */
        $data = $request->validated();

        $gradoTitulo = GradoTitulo::create($data);

        return (new GradosTitulosResource($gradoTitulo))
               ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /* ───────────────────────────────────────────────────────────── */
    /* 4. UPDATE                                                    */
    /* ───────────────────────────────────────────────────────────── */

    /**
     * @OA\Put(
     *   path="/grados-titulos/{id}",
     *   tags={"GradosTitulos"},
     *   summary="Actualizar un registro académico",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer", example=101)),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="mayor_grado_academico", type="string", example="Magíster"),
     *       @OA\Property(property="maestria",               type="string", example="MAES en Docencia")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Actualizado",
     *     @OA\JsonContent(ref="#/components/schemas/GradoTitulo")),
     *   @OA\Response(response=404, description="No encontrado"),
     *   @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function update(UpdateGradoTituloRequest $request, int $id): JsonResponse
    {
        $gt = GradoTitulo::findOrFail($id);
        $gt->update($request->validated());

        return (new GradosTitulosResource($gt->fresh(
                    'docenteNombrado','docenteContratado')))
               ->response()->setStatusCode(Response::HTTP_OK);
    }
}
