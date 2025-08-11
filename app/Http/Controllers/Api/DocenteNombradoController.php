<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexDocentesNombradosRequest;
use App\Http\Requests\CreateDocenteNombradoRequest;
use App\Http\Resources\DocenteNombradoResource;
use App\Http\Requests\UpdateDocenteNombradoRequest;
use Illuminate\Support\Facades\DB;
use App\Models\DocenteNombrado;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DocentesNombradosImport;
use OpenApi\Annotations as OA;

class DocenteNombradoController extends Controller
{
    /**
     * @OA\Get(
     *   path="/docentes-nombrados",
     *   tags={"Docentes Nombrados"},
     *   summary="Obtener lista paginada de docentes nombrados (con búsqueda opcional)",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="page",      in="query", @OA\Schema(type="integer", example=1)),
     *   @OA\Parameter(name="per_page",  in="query", @OA\Schema(type="integer", example=25)),
     *   @OA\Parameter(name="search",    in="query", description="Buscar por DNI, nombres, apellidos o facultad", @OA\Schema(type="string", example="Gómez")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Listado paginado",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array",  @OA\Items(ref="#/components/schemas/DocenteNombrado")),
     *       @OA\Property(property="meta", type="object",
     *         @OA\Property(property="current_page", type="integer"),
     *         @OA\Property(property="last_page",    type="integer"),
     *         @OA\Property(property="per_page",     type="integer"),
     *         @OA\Property(property="total",        type="integer")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Prohibido"),
     *   @OA\Response(response=500, description="Error inesperado")
     * )
     */
    public function index(IndexDocentesNombradosRequest $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 25);
        $search  = $request->input('search');

        $query = DocenteNombrado::query()
            ->with(['categoria', 'regimen', 'facultad']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('dni',               'like', "%{$search}%")
                  ->orWhere('nombres',          'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhereHas('facultad',      fn($f) => $f->where('siglas', 'like', "%{$search}%"));
            });
        }

        $collection = $query->paginate($perPage);

        return response()->json([
            'data' => DocenteNombradoResource::collection($collection),
            'meta' => [
                'current_page' => $collection->currentPage(),
                'last_page'    => $collection->lastPage(),
                'per_page'     => $collection->perPage(),
                'total'        => $collection->total(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *   path="/docentes-nombrados",
     *   tags={"Docentes Nombrados"},
     *   summary="Crear docente o importar vía archivo Excel/CSV",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="file", type="string", format="binary", description="Archivo .xlsx /.csv con plantilla de docentes"),
     *         @OA\Property(property="dni",               type="string", example="12345678"),
     *         @OA\Property(property="apellido_paterno",  type="string", example="García"),
     *         @OA\Property(property="apellido_materno",  type="string", example="Flores"),
     *         @OA\Property(property="nombres",           type="string", example="Ana María"),
     *         @OA\Property(property="categoria_id",      type="integer", example=2),
     *         @OA\Property(property="regimen_id",        type="integer", example=1),
     *         @OA\Property(property="regimen_pensionario_id", type="integer", example=3),
     *         @OA\Property(property="facultad_id",       type="integer", example=5)
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="Importación completada"),
     *   @OA\Response(response=201, description="Docente creado", @OA\JsonContent(ref="#/components/schemas/DocenteNombrado")),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Prohibido"),
     *   @OA\Response(response=422, description="Errores de validación"),
     *   @OA\Response(response=500, description="Error inesperado")
     * )
     */
    public function store(CreateDocenteNombradoRequest $request): JsonResponse
    {
        // Importación masiva
        if ($request->hasFile('file')) {
            Excel::import(new DocentesNombradosImport, $request->file('file'));

            return response()->json([
                'message' => 'Importación completada exitosamente.'
            ], 200);
        }

        // Creación individual
        $data    = $request->validated();
        unset($data['file']);

        $docente = DocenteNombrado::create($data);

        return (new DocenteNombradoResource($docente))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *   path="/docentes-nombrados/{id}",
     *   tags={"Docentes Nombrados"},
     *   summary="Mostrar un docente nombrado por ID",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=42)),
     *
     *   @OA\Response(response=200, description="Docente nombrado", @OA\JsonContent(ref="#/components/schemas/DocenteNombrado")),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Prohibido"),
     *   @OA\Response(response=404, description="Docente no encontrado"),
     *   @OA\Response(response=500, description="Error inesperado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $docente = DocenteNombrado::with([
                'categoria', 'regimen', 'regimenPensionario',
                'facultad',  'historialesCategoriaRegimen',
                'condiciones', 'procedencias', 'renacyt'
            ])
            ->findOrFail($id);

        return (new DocenteNombradoResource($docente))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * @OA\Put(
     *   path="/docentes-nombrados/{id}",
     *   tags={"Docentes Nombrados"},
     *   summary="Actualizar un docente nombrado",
     *   security={{"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID del docente nombrado",
     *     @OA\Schema(type="integer", example=5)
     *   ),
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="apellido_paterno",  type="string",  example="GÓMEZ"),
     *       @OA\Property(property="apellido_materno",  type="string",  example="LÓPEZ"),
     *       @OA\Property(property="nombres",           type="string",  example="JUAN MANUEL"),
     *       @OA\Property(property="mayor_grado_academico", type="string", example="Doctor"),
     *       @OA\Property(property="anio_ingreso_unprg", type="integer", example=2015),
     *       @OA\Property(property="fecha_nacimiento",   type="string",  format="date", example="1980-05-15"),
     *       @OA\Property(property="direccion",          type="string",  example="Av. Principal 123"),
     *       @OA\Property(property="numero_celular",     type="string",  example="987654321"),
     *       @OA\Property(property="correo_institucional", type="string", format="email", example="juan@unprg.edu.pe"),
     *       @OA\Property(property="categoria_id",           type="integer", nullable=true, example=2),
     *       @OA\Property(property="regimen_id",             type="integer", nullable=true, example=1),
     *       @OA\Property(property="regimen_pensionario_id", type="integer", nullable=true, example=3),
     *       @OA\Property(property="facultad_id",            type="integer", nullable=true, example=5),
     *
     *       @OA\Property(
     *         property="historiales",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id",               type="integer", nullable=true, example=9),
     *           @OA\Property(property="categoria_id",     type="integer", example=2),
     *           @OA\Property(property="regimen_id",       type="integer", nullable=true, example=1),
     *           @OA\Property(property="tipo_resolucion",  type="string",  enum={"NOMBRAMIENTO","ASCENSO"}, example="ASCENSO"),
     *           @OA\Property(property="nro_resolucion",   type="string",  example="R N° 123-2024-R"),
     *           @OA\Property(property="fecha_resolucion", type="string",  format="date", example="2024-01-10")
     *         )
     *       ),
     *
     *       @OA\Property(
     *         property="condiciones",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id",               type="integer", nullable=true, example=3),
     *           @OA\Property(property="condicion",        type="string",  example="FALLECIDO(A)"),
     *           @OA\Property(property="nro_resolucion",   type="string",  nullable=true, example="R N° 1115-2022-R"),
     *           @OA\Property(property="fecha_resolucion", type="string",  format="date", nullable=true, example="2022-11-02")
     *         )
     *       ),
     *
     *       @OA\Property(
     *         property="procedencias",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(property="id",               type="integer", nullable=true, example=4),
     *           @OA\Property(property="procedencia",      type="string",  example="CONCURSO PUBLICO"),
     *           @OA\Property(property="nro_resolucion",   type="string",  nullable=true),
     *           @OA\Property(property="fecha_resolucion", type="string",  format="date", nullable=true),
     *           @OA\Property(property="comentario",       type="string",  nullable=true)
     *         )
     *       ),
     *
     *       @OA\Property(
     *         property="renacyt",
     *         type="object",
     *         @OA\Property(property="nivel_renacyt",  type="string", example="III"),
     *         @OA\Property(property="nro_resolucion", type="string", nullable=true)
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Docente actualizado",
     *     @OA\JsonContent(ref="#/components/schemas/DocenteNombrado")
     *   ),
     *   @OA\Response(response=422, description="Errores de validación")
     * )
     */


    public function update(
        UpdateDocenteNombradoRequest $req,
        DocenteNombrado $docente
    ): DocenteNombradoResource {

        DB::transaction(function () use ($docente, $req) {

            $data = $req->validated();

            /* a) campos simples + FKs ---------------------------------- */
            $docente->fill(collect($data)->except([
                'historiales','condiciones','procedencias','renacyt'
            ])->all());
            $docente->save();

            /* b) Historial Cat‑Reg  (sync completo) -------------------- */
            if (array_key_exists('historiales', $data)) {
                $idsEnviados = [];

                foreach ($data['historiales'] as $h) {
                    $item = $docente->historialesCategoriaRegimen()
                                    ->updateOrCreate(
                                        ['id' => $h['id'] ?? null],
                                        collect($h)->except('id')->all()
                                    );
                    $idsEnviados[] = $item->id;     // para saber luego cuáles existen
                }

                // elimina los que el cliente quitó
                $docente->historialesCategoriaRegimen()
                        ->whereNotIn('id', $idsEnviados)
                        ->delete();
            }

            /* c) Condiciones ------------------------------------------- */
            if (array_key_exists('condiciones', $data)) {
                $ids = [];
                foreach ($data['condiciones'] as $c) {
                    $item = $docente->condiciones()
                                    ->updateOrCreate(
                                        ['id'=>$c['id'] ?? null],
                                        collect($c)->except('id')->all()
                                    );
                    $ids[] = $item->id;
                }
                $docente->condiciones()->whereNotIn('id',$ids)->delete();
            }

            /* d) Procedencias ------------------------------------------ */
            if (array_key_exists('procedencias', $data)) {
                $ids = [];
                foreach ($data['procedencias'] as $p) {
                    $item = $docente->procedencias()
                                    ->updateOrCreate(
                                        ['id'=>$p['id'] ?? null],
                                        collect($p)->except('id')->all()
                                    );
                    $ids[] = $item->id;
                }
                $docente->procedencias()->whereNotIn('id',$ids)->delete();
            }

            /* e) RENACYT ----------------------------------------------- */
            if (isset($data['renacyt'])) {
                $docente->renacyt()->updateOrCreate([], $data['renacyt']);
            }
        });

        /* ———  eager‑load completo para el resource ——— */
        $docente->load(
            'categoria',
            'regimen',
            'regimenPensionario',
            'facultad',
            'historialesCategoriaRegimen.categoria',
            'historialesCategoriaRegimen.regimen',
            'condiciones',
            'procedencias',
            'renacyt'
        );

        return new DocenteNombradoResource($docente);
    }

}

