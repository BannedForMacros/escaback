<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportContratosRequest;
use App\Http\Resources\DocenteContratadoResource;
use App\Imports\ContratosDocentesImport;
use App\Http\Requests\IndexDocentesContratadosRequest;
use App\Http\Resources\ContratoDocenteResource;
use App\Http\Requests\ShowDocenteContratadoRequest;
use App\Models\DocenteContratado;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Annotations as OA;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *   name="DocentesContratados",
 *   description="CRUD y carga masiva de docentes contratados"
 * )
 * @OA\Server(url="/api", description="API base")
 */
class DocenteContratadoController extends Controller
{
    /**
     * @OA\Get(
     *   path="/docentes-contratados",
     *   tags={"DocentesContratados"},
     *   summary="Lista paginada de docentes contratados",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="page",     in="query", @OA\Schema(type="integer", example=1)),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *   @OA\Parameter(name="q",        in="query", @OA\Schema(type="string"), description="Búsqueda sobre DNI, nombre o resolución"),
     *   @OA\Response(
     *     response=200,
     *     description="Lista paginada",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/DocenteContratado")
     *       ),
     *       @OA\Property(
     *         property="meta",
     *         type="object",
     *         @OA\Property(property="current_page", type="integer", example=1),
     *         @OA\Property(property="last_page",    type="integer", example=4),
     *         @OA\Property(property="per_page",     type="integer", example=25),
     *         @OA\Property(property="total",        type="integer", example=83)
     *       )
     *     )
     *   )
     * )
     */
    public function index(): Response
    {
        $docs = DocenteContratado::paginate(request('per_page', 25));

        return response(
            DocenteContratadoResource::collection($docs)
                ->additional([
                    'meta' => [
                        'current_page' => $docs->currentPage(),
                        'last_page'    => $docs->lastPage(),
                        'per_page'     => $docs->perPage(),
                        'total'        => $docs->total(),
                    ],
                ])
        );
    }

/**
     * @OA\Get(
     *   path="/docentes-contratados/{id}",
     *   operationId="showDocenteContratado",
     *   tags={"DocentesContratados"},
     *   summary="Detalle de un docente contratado con sus contratos agrupados por ciclo",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id", in="path",
     *     description="ID del docente contratado",
     *     required=true, @OA\Schema(type="integer", example=5)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Docente encontrado",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer"),
     *         @OA\Property(property="dni", type="string"),
     *         @OA\Property(property="nombres", type="string"),
     *         @OA\Property(
     *           property="contratos_por_ciclo", type="array",
     *           @OA\Items(
     *             @OA\Property(
     *               property="ciclo", type="object",
     *               @OA\Property(property="anio", type="integer"),
     *               @OA\Property(property="semestre", type="string")
     *             ),
     *             @OA\Property(
     *               property="contratos", type="array",
     *               @OA\Items(ref="#/components/schemas/ContratoDocente")
     *             )
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        // buscamos con todas las relaciones
        $docente = DocenteContratado::with([
            'contratos.ciclo',
            'contratos.facultad',
            'contratos.programa'
        ])->findOrFail($id);

        // agrupamos contratos por ciclo
        $grupos = $docente->contratos
            ->groupBy(fn($c) => $c->ciclo->anio . '-' . $c->ciclo->semestre)
            ->map(fn($coleccion, $clave) => [
                'ciclo'     => [
                    'anio'     => explode('-', $clave)[0],
                    'semestre' => explode('-', $clave)[1],
                ],
                'contratos' => ContratoDocenteResource::collection($coleccion)
            ])->values();

        return response()->json([
            'data' => [
                'id'        => $docente->id,
                'dni'       => $docente->dni,
                'nombres'   => $docente->nombres,
                'apellido_paterno' => $docente->apellido_paterno,
                'apellido_materno' => $docente->apellido_materno,
                'sexo'      => $docente->sexo,
                'mayor_grado_academico' => $docente->mayor_grado_academico,
                'telefono'  => $docente->telefono,
                'correo'    => $docente->correo,
                'contratos_por_ciclo' => $grupos,
            ]
        ], 200);
    }

    /* ================================================================
       CREAR
       ================================================================*/
    /**
     * @OA\Post(
     *   path="/docentes-contratados",
     *   operationId="storeDocenteContratado",
     *   tags={"DocentesContratados"},
     *   summary="Crear docente contratado",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/DocenteContratado")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Docente creado correctamente",
     *     @OA\JsonContent(ref="#/components/schemas/DocenteContratado")
     *   ),
     *   @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(Request $req): Response
    {
        $data = $req->validate([
            'dni'              => 'required|digits:8|unique:docentes_contratados,dni',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'required|string',
            'nombres'          => 'required|string',
            'sexo'             => 'nullable|in:M,F,X',
            'mayor_grado_academico' => 'nullable|string',
            'correo'           => 'nullable|email',
            'telefono'         => 'nullable|string',
        ]);

        $docente = DocenteContratado::create($data);
        return response(new DocenteContratadoResource($docente), 201);
    }

    /* ================================================================
       ACTUALIZAR
       ================================================================*/
    /**
     * @OA\Put(
     *   path="/docentes-contratados/{id}",
     *   operationId="updateDocenteContratado",
     *   tags={"DocentesContratados"},
     *   summary="Actualizar docente contratado",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/DocenteContratado")),
     *   @OA\Response(
     *     response=200,
     *     description="Docente actualizado",
     *     @OA\JsonContent(ref="#/components/schemas/DocenteContratado")
     *   ),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function update(Request $req, DocenteContratado $docente): Response
    {
        $data = $req->validate([
            'apellido_paterno' => 'sometimes|required|string',
            'apellido_materno' => 'sometimes|required|string',
            'nombres'          => 'sometimes|required|string',
            'sexo'             => 'nullable|in:M,F,X',
            'mayor_grado_academico' => 'nullable|string',
            'correo'           => 'nullable|email',
            'telefono'         => 'nullable|string',
        ]);

        $docente->update($data);
        return response(new DocenteContratadoResource($docente));
    }

    /* ================================================================
       ELIMINAR
       ================================================================*/
    /**
     * @OA\Delete(
     *   path="/docentes-contratados/{id}",
     *   operationId="deleteDocenteContratado",
     *   tags={"DocentesContratados"},
     *   summary="Eliminar docente contratado",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Eliminado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function destroy(DocenteContratado $docente): Response
    {
        $docente->delete();
        return response()->noContent();
    }

    /* ================================================================
       IMPORTAR CONTRATOS (año + semestre)
       ================================================================*/
    /**
     * @OA\Post(
     *   path="/docentes-contratados/import",
     *   operationId="importContratosDocentes",
     *   tags={"DocentesContratados"},
     *   summary="Importar Excel de contratos",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="anio", in="query", required=true, @OA\Schema(type="integer", example=2025)),
     *   @OA\Parameter(name="semestre", in="query", required=true, @OA\Schema(type="string", enum={"I","II"}, example="I")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"file"},
     *         @OA\Property(property="file", type="file", description="Archivo XLSX/XLS/CSV")
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Importación exitosa",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Archivo importado correctamente para 2025-I")
     *     )
     *   ),
     *   @OA\Response(response=422, description="Errores de validación")
     * )
     */
public function import(ImportContratosRequest $req): JsonResponse   // ⬅️ cambia aquí
{
    $anio = (int) $req->input('anio');
    $sem  =       $req->input('semestre'); // 'I' o 'II'

    Excel::import(
        new ContratosDocentesImport($anio, $sem),
        $req->file('file')
    );

    return response()->json([
        'message' => "Archivo importado correctamente para {$anio}-{$sem}"
    ]);
}
}
