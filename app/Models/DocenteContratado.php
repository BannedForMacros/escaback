<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ContratoDocente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Str;


/**
 * @OA\Schema(
 *   schema="DocenteContratado",
 *   required={"dni","apellido_paterno","apellido_materno","nombres"},
 *   @OA\Property(property="id",                type="integer", readOnly=true, example=101),
 *   @OA\Property(property="dni",               type="string",  example="42900502"),
 *   @OA\Property(property="apellido_paterno",  type="string",  example="ALVA"),
 *   @OA\Property(property="apellido_materno",  type="string",  example="ZAPATA"),
 *   @OA\Property(property="nombres",           type="string",  example="JULIANA DEL PILAR"),
 *   @OA\Property(property="sexo",              type="string",  enum={"M","F","X"}, nullable=true),
 *   @OA\Property(property="mayor_grado_academico", type="string", nullable=true, example="Maestro"),
 *   @OA\Property(property="correo",            type="string",  nullable=true, example="juliana.alva@email.com"),
 *   @OA\Property(property="telefono",          type="string",  nullable=true, example="987654321"),
 *   @OA\Property(property="created_at",        type="string", format="date-time", readOnly=true),
 *   @OA\Property(property="updated_at",        type="string", format="date-time", readOnly=true)
 * )
 */
class DocenteContratado extends Model
{
    protected $table = 'docentes_contratados';

    protected $fillable = [
        'dni','apellido_paterno','apellido_materno','nombres',
        'sexo','mayor_grado_academico','correo','telefono'
    ];

    /* relaciones */
    public function contratos()
    {
        return $this->hasMany(ContratoDocente::class, 'docente_id');
    }

/**
 * Devuelve el tiempo total de servicio del docente
 * en tramos continuos y un texto legible en español.
 *
 * @return array{
 *   tramos:      array<int, array{fecha_ingreso:string,fecha_fin:string}>,
 *   dias_total:  int,
 *   texto:       string
 * }
 */
public function getTiempoServicioAttribute(): array
{
    /*───────────────────────────────────────────────
     * 1. Contratos ordenados por fecha de inicio
     *───────────────────────────────────────────────*/
    $contratos = $this->contratos()
        ->with('ciclo')                        // anio + semestre
        ->orderBy('fecha_ingreso')
        ->orderBy('fecha_fin')
        ->get(['ciclo_id','fecha_ingreso','fecha_fin']);

    if ($contratos->isEmpty()) {
        return [
            'tramos'      => [],
            'dias_total'  => 0,
            'texto'       => '—',
        ];
    }

    /*───────────────────────────────────────────────
     * 2. Normalizamos fechas a instancias Carbon
     *───────────────────────────────────────────────*/
    $contratos = $contratos->map(function ($c) {
        // ---------- inicio ----------
        if (!$c->fecha_ingreso) {
            $mes = $c->ciclo->semestre === 'I' ? 3 : 8;           // marzo / agosto
            $c->fecha_ingreso = Carbon::create($c->ciclo->anio, $mes, 1);
        } else {
            $c->fecha_ingreso = Carbon::parse($c->fecha_ingreso);
        }

        // ---------- fin ----------
        $c->fecha_fin = $c->fecha_fin
            ? Carbon::parse($c->fecha_fin)
            : Carbon::now();

        // si las fechas vienen invertidas, las corregimos
        if ($c->fecha_ingreso->gt($c->fecha_fin)) {
            [$c->fecha_ingreso, $c->fecha_fin] = [$c->fecha_fin, $c->fecha_ingreso];
        }

        return $c;
    });

    /*───────────────────────────────────────────────
     * 3. Fusionamos tramos continuos (≤ 31 días)
     *───────────────────────────────────────────────*/
    $tramos = [];
    $actual = $contratos->first()->replicate();

    foreach ($contratos->slice(1) as $c) {
        $gap = $actual->fecha_fin->diffInDays($c->fecha_ingreso, false);

        if ($gap > 31) {                              // se interrumpe
            $tramos[] = $actual;
            $actual   = $c->replicate();
        } else {                                      // continúa
            if ($c->fecha_fin->gt($actual->fecha_fin)) {
                $actual->fecha_fin = $c->fecha_fin;
            }
        }
    }
    $tramos[] = $actual;                              // último tramo

    /*───────────────────────────────────────────────
     * 4. Cálculo total de días
     *───────────────────────────────────────────────*/
    $diasTotal = collect($tramos)->reduce(function (int $carry, $t) {
        return $carry + $t->fecha_ingreso->diffInDays($t->fecha_fin);
    }, 0);

    /*───────────────────────────────────────────────
     * 5. Conversión a A / M / D + pluralización ES
     *───────────────────────────────────────────────*/
    $anios  = intdiv($diasTotal, 365);
    $resto  = $diasTotal % 365;
    $meses  = intdiv($resto, 30);
    $dias   = $resto % 30;

    $plural = static function (string $unidad, int $n): string {
        // “año(s)”, “mes(es)”, “día(s)”
        return $n === 1
            ? $unidad
            : ($unidad === 'mes' ? 'meses' : $unidad . 's');
    };

    $partes = [];
    if ($anios)  $partes[] = "{$anios} {$plural('año', $anios)}";
    if ($meses)  $partes[] = "{$meses} {$plural('mes', $meses)}";
    if (!$anios && !$meses || $dias) {
        $partes[] = "{$dias} {$plural('día', $dias)}";
    }

    $texto = $partes ? implode(' ', $partes) : '—';

    /*───────────────────────────────────────────────
     * 6. Salida
     *───────────────────────────────────────────────*/
    return [
        'tramos'      => collect($tramos)->map(fn ($t) => [
            'fecha_ingreso' => $t->fecha_ingreso->toDateString(),
            'fecha_fin'     => $t->fecha_fin->toDateString(),
        ])->values(),
        'dias_total'  => $diasTotal,
        'texto'       => $texto,
    ];
}


}
